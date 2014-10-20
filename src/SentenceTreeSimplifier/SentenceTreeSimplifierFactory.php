<?php

namespace PPP\Wikidata\SentenceTreeSimplifier;

use Mediawiki\Api\MediawikiApi;
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
class SentenceTreeSimplifierFactory {
	/**
	 * @var MediawikiApi
	 */
	private $mediawikiApi;

	/**
	 * @var WikidataQueryApi
	 */
	private $wikidataQueryApi;

	/**
	 * @param MediawikiApi $mediawikiApi
	 * @param WikidataQueryApi $wikidataQueryApi
	 */
	public function __construct(MediawikiApi $mediawikiApi, WikidataQueryApi $wikidataQueryApi) {
		$this->mediawikiApi = $mediawikiApi;
		$this->wikidataQueryApi = $wikidataQueryApi;
	}

	/**
	 * @return SentenceTreeSimplifier
	 */
	public function newSentenceTreeSimplifier() {
		return new SentenceTreeSimplifier(array(
			$this->newMissingObjectTripleNodeSimplifier(),
			$this->newMissingSubjectTripleNodeSimplifier()
		));
	}

	private function newMissingObjectTripleNodeSimplifier() {
		return new MissingObjectTripleNodeSimplifier($this->newEntityProvider());
	}

	private function newMissingSubjectTripleNodeSimplifier() {
		$wikidataQueryFactory = new WikidataQueryFactory($this->wikidataQueryApi);
		return new MissingSubjectTripleNodeSimplifier($wikidataQueryFactory->newSimpleQueryService());
	}

	private function newEntityProvider() {
		$wikibaseFactory = new WikibaseFactory($this->mediawikiApi);
		return new WikibaseEntityProvider($wikibaseFactory->newRevisionGetter());
	}
}