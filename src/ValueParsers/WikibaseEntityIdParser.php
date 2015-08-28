<?php

namespace PPP\Wikidata\ValueParsers;

use InvalidArgumentException;
use ValueParsers\ParserOptions;
use ValueParsers\StringValueParser;
use ValueParsers\ValueParser;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Term\Term;
use Wikibase\EntityStore\EntityStore;

/**
 * Try to find a Wikibase entities from a given string.
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseEntityIdParser extends StringValueParser {

	const FORMAT_NAME = 'wikibase-entity';

	private static $INSTANCES_TO_FILTER = array('Q4167410', 'Q17362920', 'Q4167836', 'Q13406463', 'Q11266439', 'Q14204246');

	const INSTANCEOF_PID = 'P31';

	/**
	 * Identifier for the option that holds the type of entity the parser should looks for.
	 */
	const OPT_ENTITY_TYPE = 'type';

	/**
	 * @var EntityStore
	 */
	private $entityStore;

	/**
	 * @param EntityStore $entityStore
	 * @param ParserOptions|null $options
	 */
	public function __construct(EntityStore $entityStore, ParserOptions $options = null) {
		$options->requireOption(self::OPT_ENTITY_TYPE);

		$this->entityStore = $entityStore;
		parent::__construct($options);
	}

	protected function stringParse($value) {
		if($value === '') {
			return array();
		}

		return $this->entityIdsToEntityIdValues($this->getEntityIdsForValue($value));
	}

	private function getEntityIdsForValue($value) {
		$languageCode = $this->getOption(ValueParser::OPT_LANG);
		$term = new Term($languageCode, $value);

		$entityType = $this->getOption(self::OPT_ENTITY_TYPE);
		switch($entityType) {
			case 'item':
				return $this->getItemIdsForTerm($term);
			case 'property':
				return $this->getPropertyIdsForTerm($term);
			default:
				throw new InvalidArgumentException('Unknown entity type ' . $entityType);
		}
	}

	private function getItemIdsForTerm(Term $term) {
		$itemIds = $this->filterItemIds(
			$this->entityStore->getItemIdForTermLookup()->getItemIdsForTerm($term)
		);

		try {
			$itemIds[] = new ItemId($term->getText());
		} catch(InvalidArgumentException $e) {
			//The term is not a valid QID
		}

		return $this->sortItemIds(array_unique($itemIds));
	}

	private function getPropertyIdsForTerm(Term $term) {
		$propertyIds = $this->entityStore->getPropertyIdForTermLookup()->getPropertyIdsForTerm($term);

		try {
			$propertyIds[] = new PropertyId($term->getText());
		} catch(InvalidArgumentException $e) {
			//The term is not a valid PID
		}

		return array_unique($propertyIds);
	}

	private function filterItemIds(array $itemIds) {
		$entities = $this->entityStore->getEntityDocumentLookup()->getEntityDocumentsForIds($itemIds);
		$filtered = array();

		foreach($entities as $entity) {
			if($entity instanceof Item && !$this->shouldItemBeIgnored($entity)) {
				$filtered[] = $entity->getId();
			}
		}

		return $filtered;
	}

	private function shouldItemBeIgnored(Item $item) {
		/** @var Statement $statement */
		foreach($item->getStatements()->getWithPropertyId(new PropertyId(self::INSTANCEOF_PID)) as $statement) {
			$mainSnak = $statement->getMainSnak();
			if(
				$mainSnak instanceof PropertyValueSnak &&
				$mainSnak->getDataValue() instanceof EntityIdValue &&
				in_array($mainSnak->getDataValue()->getEntityId()->getSerialization(), self::$INSTANCES_TO_FILTER)
			) {
				return true;
			}
		}

		return false;
	}

	private function entityIdsToEntityIdValues(array $entityIds) {
		$values = array();
		foreach($entityIds as $entityId) {
			$values[] = new EntityIdValue($entityId);
		}

		return $values;
	}


	private function sortItemIds(array $itemIds) {
		usort(
			$itemIds,
			function(ItemId $a, ItemId $b) {
				return $a->getNumericId() - $b->getNumericId();
			}
		);

		return $itemIds;
	}
}
