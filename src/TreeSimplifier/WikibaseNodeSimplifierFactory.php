<?php

namespace PPP\Wikidata\TreeSimplifier;

use Doctrine\Common\Cache\Cache;
use Mediawiki\Api\MediawikiApi;
use PPP\Module\TreeSimplifier\NodeSimplifierFactory;
use PPP\Wikidata\Cache\WikibaseEntityCache;
use PPP\Wikidata\WikibaseEntityProvider;
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
	 * @var MediawikiApi
	 */
	private $mediawikiApi;

	/**
	 * @var WikidataQueryApi
	 */
	private $wikidataQueryApi;

	/**
	 * @var Cache
	 */
	private $cache;

	/**
	 * @param MediawikiApi $mediawikiApi
	 * @param WikidataQueryApi $wikidataQueryApi
	 */
	public function __construct(MediawikiApi $mediawikiApi, WikidataQueryApi $wikidataQueryApi, Cache $cache) {
		$this->mediawikiApi = $mediawikiApi;
		$this->wikidataQueryApi = $wikidataQueryApi;
		$this->cache = $cache;

		parent::__construct(array(
			$this->newMissingObjectTripleNodeSimplifier(),
			$this->newMissingSubjectTripleNodeSimplifier()
		));
	}

	private function newMissingObjectTripleNodeSimplifier() {
		return new MissingObjectTripleNodeSimplifier($this, $this->newEntityProvider());
	}

	private function newMissingSubjectTripleNodeSimplifier() {
		$wikidataQueryFactory = new WikidataQueryFactory($this->wikidataQueryApi);
		return new MissingSubjectTripleNodeSimplifier($this, $wikidataQueryFactory->newSimpleQueryService());
	}

	private function newEntityProvider() {
		$wikibaseFactory = new WikibaseFactory($this->mediawikiApi);
		return new WikibaseEntityProvider(
			$wikibaseFactory->newRevisionGetter(),
			new WikibaseEntityCache($this->cache)
		);
	}
}
