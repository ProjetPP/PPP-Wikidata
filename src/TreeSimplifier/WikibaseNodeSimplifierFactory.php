<?php

namespace PPP\Wikidata\TreeSimplifier;

use Doctrine\Common\Cache\Cache;
use Mediawiki\Api\MediawikiApi;
use PPP\Module\TreeSimplifier\IntersectionNodeSimplifier;
use PPP\Module\TreeSimplifier\NodeSimplifierFactory;
use PPP\Wikidata\ValueParsers\ResourceListNodeParser;
use PPP\Wikidata\ValueParsers\WikibaseValueParserFactory;
use Wikibase\EntityStore\Api\ApiEntityStore;
use Wikibase\EntityStore\Cache\CachedEntityStore;
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
			$this->newSentenceNodeSimplifier($mediawikiApi, $cache, $languageCode),
			$this->newMeaninglessPredicateTripleNodeSimplifier($mediawikiApi, $cache, $languageCode),
			$this->newMissingObjectTripleNodeSimplifier($mediawikiApi, $cache, $languageCode),
			$this->newMissingSubjectTripleNodeSimplifier($wikidataQueryApi, $mediawikiApi, $cache, $languageCode),
			$this->newIntersectionWithFilterNodeSimplifier($mediawikiApi, $cache, $languageCode)
		));
	}

	private function newSentenceNodeSimplifier(MediawikiApi $mediawikiApi, Cache $cache, $languageCode) {
		return new SentenceNodeSimplifier($this->newResourceListNodeParser($mediawikiApi, $cache, $languageCode));
	}

	private function newMeaninglessPredicateTripleNodeSimplifier(MediawikiApi $mediawikiApi, Cache $cache, $languageCode) {
		return new SpecificTripleNodeSimplifier($this->newResourceListNodeParser($mediawikiApi, $cache, $languageCode));
	}

	private function newMissingObjectTripleNodeSimplifier(MediawikiApi $mediawikiApi, Cache $cache, $languageCode) {
		return new MissingObjectTripleNodeSimplifier(
			$this->newResourceListNodeParser($mediawikiApi, $cache, $languageCode),
			$this->newEntityStore($mediawikiApi, $cache)
		);
	}

	private function newMissingSubjectTripleNodeSimplifier(WikidataQueryApi $wikidataQueryApi, MediawikiApi $mediawikiApi, Cache $cache, $languageCode) {
		$wikidataQueryFactory = new WikidataQueryFactory($wikidataQueryApi);
		return new MissingSubjectTripleNodeSimplifier(
			$this,
			$wikidataQueryFactory->newSimpleQueryService(),
			$this->newEntityStore($mediawikiApi, $cache),
			$this->newResourceListNodeParser($mediawikiApi, $cache, $languageCode)
		);
	}

	private function newIntersectionWithFilterNodeSimplifier(MediawikiApi $mediawikiApi, Cache $cache, $languageCode) {
		return new IntersectionWithFilterNodeSimplifier(
			new IntersectionNodeSimplifier($this),
			$this->newEntityStore($mediawikiApi, $cache),
			$this->newResourceListNodeParser($mediawikiApi, $cache, $languageCode)
		);
	}

	private function newEntityStore(MediawikiApi $mediawikiApi, Cache $cache) {
		return new CachedEntityStore(new ApiEntityStore($mediawikiApi), $cache);
	}

	private function newResourceListNodeParser(MediawikiApi $mediawikiApi, Cache $cache, $languageCode) {
		$parserFactory = new WikibaseValueParserFactory($languageCode, new CachedEntityStore(new ApiEntityStore($mediawikiApi), $cache));
		return new ResourceListNodeParser($parserFactory->newWikibaseValueParser());
	}
}
