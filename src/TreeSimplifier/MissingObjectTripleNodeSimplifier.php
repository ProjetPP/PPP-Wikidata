<?php

namespace PPP\Wikidata\TreeSimplifier;

use InvalidArgumentException;
use OutOfBoundsException;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\TripleNode;
use PPP\Module\TreeSimplifier\NodeSimplifier;
use PPP\Wikidata\ValueParsers\ResourceListNodeParser;
use PPP\Wikidata\WikibaseResourceNode;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Snak\Snak;
use Wikibase\DataModel\Statement\BestStatementsFinder;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\EntityStore\EntityStore;

/**
 * Simplifies a triple node when the object is missing.
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class MissingObjectTripleNodeSimplifier implements NodeSimplifier {

	/**
	 * @var ResourceListNodeParser
	 */
	private $resourceListNodeParser;

	/**
	 * @var EntityStore
	 */
	private $entityStore;

	/**
	 * @param ResourceListNodeParser $resourceListNodeParser
	 * @param EntityStore $entityStore
	 */
	public function __construct(ResourceListNodeParser $resourceListNodeParser, EntityStore $entityStore) {
		$this->resourceListNodeParser = $resourceListNodeParser;
		$this->entityStore = $entityStore;
	}

	/**
	 * @see NodeSimplifier::isSimplifierFor
	 */
	public function isSimplifierFor(AbstractNode $node) {
		return $node instanceof TripleNode &&
			$node->getSubject() instanceof ResourceListNode &&
			$node->getPredicate() instanceof ResourceListNode &&
			$node->getObject() instanceof MissingNode;
	}

	/**
	 * @see NodeSimplifier::doSimplification
	 */
	public function simplify(AbstractNode $node) {
		if(!$this->isSimplifierFor($node)) {
			throw new InvalidArgumentException('MissingObjectTripleNodeSimplifier can only simplify TripleNode with a missing object');
		}

		return $this->doSimplification($node);
	}

	private function doSimplification(TripleNode $node) {
		$subjectNodes = $this->resourceListNodeParser->parse($node->getSubject(), 'wikibase-item');
		$propertyNodes = $this->resourceListNodeParser->parse($node->getPredicate(), 'wikibase-property');
		$results = array();

		foreach($subjectNodes as $subject) {
			foreach($propertyNodes as $predicate) {
				$results = array_merge(
					$results,
					$this->getNodesForObject($subject, $predicate)
				);
			}
		}

		$node = new ResourceListNode($results);
		$this->loadEntitiesFromNode($node);
		return $node;
	}

	protected function getNodesForObject(WikibaseResourceNode $subject, WikibaseResourceNode $predicate) {
		/** @var ItemId $itemId */
		$itemId = $subject->getDataValue()->getEntityId();
		/** @var PropertyId $propertyId */
		$propertyId = $predicate->getDataValue()->getEntityId();

		$item = $this->entityStore->getItemLookup()->getItemForId($itemId);
		$snaks = $this->getSnaksForProperty($item, $propertyId);
		return $this->snaksToNodes($snaks, $itemId, $propertyId);
	}

	/**
	 * @return Snak[]
	 */
	private function getSnaksForProperty(Item $item, PropertyId $propertyId) {
		$statementFinder = new BestStatementsFinder($item->getStatements());

		try {
			$statements = $statementFinder->getBestStatementsForProperty($propertyId);
		} catch(OutOfBoundsException $e) {
			return array();
		}

		$snaks = array();
		/** @var Statement $statement */
		foreach($statements as $statement) {
			$snaks[] = $statement->getMainSnak();
		}

		return $snaks;
	}

	private function loadEntitiesFromNode(ResourceListNode $nodes) {
		$entityIds = array();

		/** @var WikibaseResourceNode $node */
		foreach($nodes as $node) {
			$value =  $node->getDataValue();

			if($value instanceof EntityIdValue) {
				$entityIds[] = $value->getEntityId();
			}
		}
	}

	private function snaksToNodes(array $snaks, EntityId $fromSubject, PropertyId $fromProperty) {
		$nodes = array();

		foreach($snaks as $snak) {
			if($snak instanceof PropertyValueSnak) {
				$nodes[] = new WikibaseResourceNode('', $snak->getDataValue(), $fromSubject, $fromProperty);
			}
			//TODO case of PropertySomeValueSnak (MissingNode) and PropertyNoValueSnak (return the negation of the triple?)
		}

		return $nodes;
	}
}
