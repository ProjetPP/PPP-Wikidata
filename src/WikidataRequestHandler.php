<?php

namespace PPP\Wikidata;

use Doctrine\Common\Cache\Cache;
use Mediawiki\Api\MediawikiApi;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\ResourceListNode;
use PPP\Module\AbstractRequestHandler;
use PPP\Module\DataModel\ModuleRequest;
use PPP\Module\DataModel\ModuleResponse;
use PPP\Module\TreeSimplifier\NodeSimplifierFactory;
use PPP\Wikidata\DataModel\Deserializers\WikibaseEntityResourceNodeDeserializer;
use PPP\Wikidata\DataModel\Serializers\WikibaseEntityResourceNodeSerializer;
use PPP\Wikidata\TreeSimplifier\WikibaseNodeSimplifierFactory;
use PPP\Wikidata\ValueFormatters\WikibaseValueFormatterFactory;
use Wikibase\DataModel\Entity\BasicEntityIdParser;
use WikidataQueryApi\WikidataQueryApi;

/**
 * Module entry point.
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikidataRequestHandler extends AbstractRequestHandler {

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
		$simplifiedTree = $this->buildTreeSimplifier($request->getLanguageCode())->simplify($request->getSentenceTree());

		$formattedTree = $this->buildNodeFormatter($request->getLanguageCode())->simplify($simplifiedTree);

		if($formattedTree->equals(new ResourceListNode())) {
			return array();
		}

		return array(new ModuleResponse(
			$request->getLanguageCode(),
			$formattedTree,
			$this->buildMeasures($formattedTree, $request->getMeasures())
		));
	}

	private function buildMeasures(AbstractNode $node, array $measures) {
		if(array_key_exists('accuracy', $measures)) {
			$measures['accuracy'] /= 2;
		}

		if($node instanceof ResourceListNode) {
			$measures['relevance'] = 1;
		}

		return $measures;
	}

	private function buildTreeSimplifier($languageCode) {
		$factory = new WikibaseNodeSimplifierFactory($this->mediawikiApi, $this->wikidataQueryApi, $this->cache, $languageCode);
		return $factory->newNodeSimplifier();
	}

	private function buildNodeFormatter($languageCode) {
		$formatterFactory = new WikibaseValueFormatterFactory($languageCode, $this->mediawikiApi, $this->cache);
		$simplifierFactory = new NodeSimplifierFactory(array(
			new ResourceListNodeFormatter($formatterFactory->newWikibaseValueFormatter())
		));
		return $simplifierFactory->newNodeSimplifier();
	}

	/**
	 * @see RequestHandler::getCustomResourceNodeSerializers
	 */
	public function getCustomResourceNodeSerializers() {
		return array(
			new WikibaseEntityResourceNodeSerializer()
		);
	}

	/**
	 * @see RequestHandler::getCustomResourceNodeDeserializers
	 */
	public function getCustomResourceNodeDeserializers() {
		return array(
			new WikibaseEntityResourceNodeDeserializer(new BasicEntityIdParser())
		);
	}
}
