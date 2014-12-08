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
			$this->newTripleConverter($mediawikiApi, $cache, $languageCode),
			$this->newMissingObjectTripleNodeSimplifier($mediawikiApi, $cache),
			$this->newMissingSubjectTripleNodeSimplifier($wikidataQueryApi)
		));
	}

	private function newMissingObjectTripleNodeSimplifier(MediawikiApi $mediawikiApi, Cache $cache) {
		return new MissingObjectTripleNodeSimplifier($this->newEntityProvider($mediawikiApi, $cache));
	}

	private function newMissingSubjectTripleNodeSimplifier(WikidataQueryApi $wikidataQueryApi) {
		$wikidataQueryFactory = new WikidataQueryFactory($wikidataQueryApi);
		return new MissingSubjectTripleNodeSimplifier($wikidataQueryFactory->newSimpleQueryService());
	}

	private function newTripleConverter(MediawikiApi $mediawikiApi, Cache $cache, $languageCode) {
		$parserFactory = new WikibaseValueParserFactory($languageCode, $mediawikiApi, $cache);
		return new WikibaseTripleConverter(
			new ResourceListNodeParser($parserFactory->newWikibaseValueParser()),
			$this->newPropertyTypeProvider($mediawikiApi, $cache)
		);
	}

	private function newPropertyTypeProvider(MediawikiApi $mediawikiApi, Cache $cache) {
		$wikibaseFactory = new WikibaseFactory($mediawikiApi);
		return new WikibasePropertyTypeProvider(new WikibaseEntityProvider(
			$wikibaseFactory->newRevisionGetter(),
			new WikibaseEntityCache($cache)
		));
	}

	private function newEntityProvider(MediawikiApi $mediawikiApi, Cache $cache) {
		$wikibaseFactory = new WikibaseFactory($mediawikiApi);
		return new WikibaseEntityProvider(
			$wikibaseFactory->newRevisionGetter(),
			new WikibaseEntityCache($cache)
		);
	}
}
