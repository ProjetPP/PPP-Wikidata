<?php

namespace PPP\Wikidata\TreeSimplifier;

use PPP\Module\TreeSimplifier\IntersectionNodeSimplifier;
use PPP\Module\TreeSimplifier\NodeSimplifierFactory;
use PPP\Wikidata\ValueParsers\ResourceListNodeParser;
use PPP\Wikidata\ValueParsers\WikibaseValueParserFactory;
use Wikibase\EntityStore\EntityStore;
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
	 * @param EntityStore $entityStore
	 * @param WikidataQueryApi $wikidataQueryApi
	 * @param string $languageCode
	 */
	public function __construct(EntityStore $entityStore, WikidataQueryApi $wikidataQueryApi, $languageCode) {
		parent::__construct(array(
			$this->newSentenceNodeSimplifier($entityStore, $languageCode),
			$this->newMeaninglessPredicateTripleNodeSimplifier($entityStore, $languageCode),
			$this->newMissingObjectTripleNodeSimplifier($entityStore, $languageCode),
			$this->newMissingSubjectTripleNodeSimplifier($wikidataQueryApi, $entityStore, $languageCode),
			$this->newIntersectionWithFilterNodeSimplifier($entityStore, $languageCode)
		));
	}

	private function newSentenceNodeSimplifier(EntityStore $entityStore, $languageCode) {
		return new SentenceNodeSimplifier($this->newResourceListNodeParser($entityStore, $languageCode));
	}

	private function newMeaninglessPredicateTripleNodeSimplifier(EntityStore $entityStore, $languageCode) {
		return new SpecificTripleNodeSimplifier($this->newResourceListNodeParser($entityStore, $languageCode));
	}

	private function newMissingObjectTripleNodeSimplifier(EntityStore $entityStore, $languageCode) {
		return new MissingObjectTripleNodeSimplifier(
			$this->newResourceListNodeParser($entityStore, $languageCode),
			$entityStore
		);
	}

	private function newMissingSubjectTripleNodeSimplifier(WikidataQueryApi $wikidataQueryApi, EntityStore $entityStore, $languageCode) {
		$wikidataQueryFactory = new WikidataQueryFactory($wikidataQueryApi);
		return new MissingSubjectTripleNodeSimplifier(
			$this,
			$wikidataQueryFactory->newSimpleQueryService(),
			$entityStore,
			$this->newResourceListNodeParser($entityStore, $languageCode)
		);
	}

	private function newIntersectionWithFilterNodeSimplifier(EntityStore $entityStore, $languageCode) {
		return new IntersectionWithFilterNodeSimplifier(
			new IntersectionNodeSimplifier($this),
			$entityStore,
			$this->newResourceListNodeParser($entityStore, $languageCode)
		);
	}

	private function newResourceListNodeParser(EntityStore $entityStore, $languageCode) {
		$parserFactory = new WikibaseValueParserFactory($languageCode, $entityStore);
		return new ResourceListNodeParser($parserFactory->newWikibaseValueParser());
	}
}
