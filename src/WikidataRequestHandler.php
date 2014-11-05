<?php

namespace PPP\Wikidata;

use Doctrine\Common\Cache\Cache;
use Mediawiki\Api\MediawikiApi;
use PPP\Module\DataModel\ModuleRequest;
use PPP\Module\DataModel\ModuleResponse;
use PPP\Module\RequestHandler;
use PPP\Wikidata\Cache\WikibaseEntityCache;
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

	/**
	 * @var Cache
	 */
	public $cache;

	public function __construct($mediawikiApiUrl, $wikidataQueryUrl, Cache $cache) {
		$this->mediawikiApi = new MediawikiApi($mediawikiApiUrl);
		$this->wikidataQueryApi = new WikidataQueryApi($wikidataQueryUrl);
		$this->cache = $cache;
	}
	/**
	 * @see RequestHandler::buildResponse
	 */
	public function buildResponse(ModuleRequest $request) {
		try {
			$annotatedTrees = $this->buildNodeAnnotator($request->getLanguageCode())->annotateNode($request->getSentenceTree());
		} catch(ParseException $e) {
			return array();
		}

		$treeSimplifier = $this->buildTreeSimplifier();
		$simplifiedTrees = array();
		try {
			foreach($annotatedTrees as $tree) {
				$simplifiedTrees += $treeSimplifier->simplify($tree);
			}
		} catch(SimplifierException $e) {
			return array();
		}

		$nodeFormatter = $this->buildNodeFormatter($request->getLanguageCode());
		$responses = array();
		foreach($simplifiedTrees as $tree) {
			$responses[] = new ModuleResponse($request->getLanguageCode(), $nodeFormatter->formatNode($tree));
		}

		return $responses;
	}

	private function buildNodeAnnotator($languageCode) {
		$parserFactory = new WikibaseValueParserFactory($languageCode, $this->mediawikiApi);
		return new WikibaseNodeAnnotator($parserFactory->newWikibaseValueParser(), $this->buildPropertyTypeProvider());
	}

	private function buildPropertyTypeProvider() {
		$wikibaseFactory = new WikibaseFactory($this->mediawikiApi);
		return new WikibasePropertyTypeProvider(new WikibaseEntityProvider(
			$wikibaseFactory->newRevisionGetter(),
			new WikibaseEntityCache($this->cache)
		));
	}

	private function buildTreeSimplifier() {
		$factory = new SentenceTreeSimplifierFactory($this->mediawikiApi, $this->wikidataQueryApi, $this->cache);
		return $factory->newSentenceTreeSimplifier();
	}

	private function buildNodeFormatter($languageCode) {
		$formatterFactory = new WikibaseValueFormatterFactory($languageCode, $this->mediawikiApi, $this->cache);
		return new WikibaseNodeFormatter($formatterFactory->newWikibaseValueFormatter());
	}
}
