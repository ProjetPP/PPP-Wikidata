<?php

namespace PPP\Wikidata;

use Mediawiki\Api\MediawikiApi;
use PPP\Module\DataModel\ModuleRequest;
use PPP\Module\DataModel\ModuleResponse;
use PPP\Module\RequestHandler;
use PPP\Wikidata\SentenceTreeSimplifier\SentenceTreeSimplifierFactory;
use PPP\Wikidata\SentenceTreeSimplifier\SimplifierException;
use PPP\Wikidata\ValueFormatters\WikibaseValueFormatterFactory;
use PPP\Wikidata\ValueParsers\WikibaseValueParserFactory;
use ValueParsers\ParseException;
use Wikibase\Api\WikibaseFactory;
use WikidataQueryApi\WikidataQueryApi;

/**
 * Module entry point.
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikidataRequestHandler implements RequestHandler {

	/**
	 * @var MediawikiApi
	 */
	public $mediawikiApi;

	/**
	 * @var WikidataQueryApi
	 */
	public $wikidataQueryApi;

	public function __construct($mediawikiApiUrl, $wikidataQueryUrl) {
		$this->mediawikiApi = new MediawikiApi($mediawikiApiUrl);
		$this->wikidataQueryApi = new WikidataQueryApi($wikidataQueryUrl);
	}
	/**
	 * @see RequestHandler::buildResponse
	 */
	public function buildResponse(ModuleRequest $request) {
		try {
			$tree = $this->buildNodeAnnotator($request->getLanguageCode())->annotateNode($request->getSentenceTree());
		} catch(ParseException $e) {
			return array();
		}

		try {
			$tree = $this->buildTreeSimplifier()->simplify($tree);
		} catch(SimplifierException $e) {
			return array();
		}

		$tree = $this->buildNodeFormatter($request->getLanguageCode())->formatNode($tree);

		return array(new ModuleResponse(
			$request->getLanguageCode(),
			$tree
		));
	}

	private function buildNodeAnnotator($languageCode) {
		$parserFactory = new WikibaseValueParserFactory($languageCode, $this->mediawikiApi);
		return new WikibaseNodeAnnotator($parserFactory->newWikibaseValueParser(), $this->buildPropertyTypeProvider());
	}

	private function buildPropertyTypeProvider() {
		$wikibaseFactory = new WikibaseFactory($this->mediawikiApi);
		return new WikibasePropertyTypeProvider(new WikibaseEntityProvider($wikibaseFactory->newRevisionGetter()));
	}

	private function buildTreeSimplifier() {
		$factory = new SentenceTreeSimplifierFactory($this->mediawikiApi, $this->wikidataQueryApi);
		return $factory->newSentenceTreeSimplifier();
	}

	private function buildNodeFormatter($languageCode) {
		$formatterFactory = new WikibaseValueFormatterFactory($languageCode, $this->mediawikiApi);
		return new WikibaseNodeFormatter($formatterFactory->newWikibaseValueFormatter());
	}
}
