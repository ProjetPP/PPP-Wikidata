<?php

namespace PPP\Wikidata\TreeSimplifier;

use Doctrine\Common\Cache\Cache;
use Mediawiki\Api\MediawikiApi;
use PPP\Module\TreeSimplifier\NodeSimplifierFactory;
use PPP\Wikidata\Cache\WikibaseEntityCache;
use PPP\Wikidata\ValueParsers\ResourceListNodeParser;
use PPP\Wikidata\ValueParsers\WikibaseValueParserFactory;
use PPP\Wikidata\WikibaseEntityProvider;
use PPP\Wikidata\WikibasePropertyTypeProvider;
use Wikibase\Api\WikibaseFactory;
use WikidataQueryApi\WikidataQueryApi;
use WikidataQueryApi\WikidataQueryFactory;

/**
 * Build a SentenceTreeSimplifier
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 *
 * @todo tests
 */
class WikibaseNodeSimplifierFactory extends NodeSimplifierFactory {

	/**
	 * @param MediawikiApi $mediawikiApi
	 * @param WikidataQueryApi $wikidataQueryApi
	 * @param Cache $cache
	 * @param string $languageCode
	 */
	public function __construct(MediawikiApi $mediawikiApi, WikidataQueryApi $wikidataQueryApi, Cache $cache, $languageCode) {
		parent::__construct(array(
			new MeaninglessPredicateTripleNodeSimplifier(),
			$this->newIdentityTripleNodeSimplifier($mediawikiApi, $cache, $languageCode),
			$this->newTripleConverter($mediawikiApi, $cache, $languageCode),
			$this->newMissingObjectTripleNodeSimplifier($mediawikiApi, $cache),
			$this->newMissingSubjectTripleNodeSimplifier($wikidataQueryApi)
		));
	}

	private function newIdentityTripleNodeSimplifier(MediawikiApi $mediawikiApi, Cache $cache, $languageCode) {
		return new IdentityTripleNodeSimplifier(
			$this->newResourceListNodeParser($mediawikiApi, $cache, $languageCode),
			$this->newEntityProvider($mediawikiApi, $cache),
			$languageCode
		);
	}

	private function newMissingObjectTripleNodeSimplifier(MediawikiApi $mediawikiApi, Cache $cache) {
		return new MissingObjectTripleNodeSimplifier($this->newEntityProvider($mediawikiApi, $cache));
	}

	private function newMissingSubjectTripleNodeSimplifier(WikidataQueryApi $wikidataQueryApi) {
		$wikidataQueryFactory = new WikidataQueryFactory($wikidataQueryApi);
		return new MissingSubjectTripleNodeSimplifier($wikidataQueryFactory->newSimpleQueryService());
	}

	private function newTripleConverter(MediawikiApi $mediawikiApi, Cache $cache, $languageCode) {
		return new WikibaseTripleConverter(
			$this->newResourceListNodeParser($mediawikiApi, $cache, $languageCode),
			new WikibasePropertyTypeProvider($this->newEntityProvider($mediawikiApi, $cache))
		);
	}

	private function newResourceListNodeParser(MediawikiApi $mediawikiApi, Cache $cache, $languageCode) {
		$parserFactory = new WikibaseValueParserFactory($languageCode, $mediawikiApi, $cache);
		return new ResourceListNodeParser($parserFactory->newWikibaseValueParser());
	}

	private function newEntityProvider(MediawikiApi $mediawikiApi, Cache $cache) {
		$wikibaseFactory = new WikibaseFactory($mediawikiApi);
		return new WikibaseEntityProvider(
			$wikibaseFactory->newRevisionGetter(),
			new WikibaseEntityCache($cache)
		);
	}
}
