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
use PPP\Wikidata\TreeSimplifier\WikibaseNodeSimplifierFactory;
use PPP\Wikidata\ValueFormatters\WikibaseResourceNodeFormatterFactory;
use Wikibase\EntityStore\Config\EntityStoreFromConfigurationBuilder;
use Wikibase\EntityStore\EntityStore;

/**
 * Module entry point.
 *
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class WikidataRequestHandler extends AbstractRequestHandler {

	/**
	 * @var EntityStore
	 */
	public $entityStore;

	/**
	 * @var MediawikiApi[]
	 */
	private $sitesApi;

	/**
	 * @var Cache
	 */
	public $cache;

	/**
	 * @var int
	 */
	private $requestStartTime;

	/**
	 * @param $configFileName
	 * @param string[] $sitesUrls
	 */
	public function __construct($configFileName, array $sitesUrls) {
		$configurationBuilder = new EntityStoreFromConfigurationBuilder();
		$this->entityStore = $configurationBuilder->buildEntityStore($configFileName);
		$this->cache = $configurationBuilder->buildCache($configFileName);
		$this->requestStartTime = time();

		$this->sitesApi = array();
		foreach($sitesUrls as $siteId => $url) {
			$this->sitesApi[$siteId] = new MediawikiApi($url);
		}
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

		$measures = $this->buildMeasures($formattedTree, $request->getMeasures());

		$trace = $request->getTrace();
		array_unshift($trace, array(
			'module' => 'Wikidata',
			'tree' => $formattedTree,
			'measures' => $measures,
			'times' => array(
				'start' => $this->requestStartTime,
				'end' => time()
			)
		));

		return array(new ModuleResponse(
			$request->getLanguageCode(),
			$formattedTree,
			$measures,
			$trace
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
		$factory = new WikibaseNodeSimplifierFactory(
			$this->entityStore,
			$languageCode
		);
		return $factory->newNodeSimplifier();
	}

	private function buildNodeFormatter($languageCode) {
		$formatterFactory = new WikibaseResourceNodeFormatterFactory($languageCode, $this->entityStore, $this->sitesApi, $this->cache);
		$simplifierFactory = new NodeSimplifierFactory(array(
			new ResourceListNodeFormatter($formatterFactory->newWikibaseResourceNodeFormatter())
		));
		return $simplifierFactory->newNodeSimplifier();
	}
}
