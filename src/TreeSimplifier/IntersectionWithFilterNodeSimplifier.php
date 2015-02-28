<?php

namespace PPP\Wikidata\TreeSimplifier;

use InvalidArgumentException;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\IntersectionNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\TripleNode;
use PPP\Module\TreeSimplifier\NodeSimplifier;
use PPP\Module\TreeSimplifier\NodeSimplifierFactory;
use PPP\Wikidata\ValueParsers\ResourceListNodeParser;
use PPP\Wikidata\WikibaseResourceNode;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Snak\SnakList;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\EntityStore\EntityStore;

/**
 * Simplifies cases like [Q42, Q43] ∩ (?, instanceof, human) without requests to WikidataQuery: just check that the items
 * have PropertyValueSnak(instanceof, human).
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class IntersectionWithFilterNodeSimplifier implements NodeSimplifier {

	/**
	 * @var NodeSimplifierFactory
	 */
	private $nodeSimplifierFactory;

	/**
	 * @var EntityStore
	 */
	private $entityStore;

	/**
	 * @var ResourceListNodeParser
	 */
	private $resourceListNodeParser;

	/**
	 * @param NodeSimplifierFactory $nodeSimplifierFactory
	 * @param EntityStore $entityStore
	 * @param ResourceListNodeParser $resourceListNodeParser
	 */
	public function __construct(NodeSimplifierFactory $nodeSimplifierFactory, EntityStore $entityStore, ResourceListNodeParser $resourceListNodeParser) {
		$this->nodeSimplifierFactory = $nodeSimplifierFactory;
		$this->entityStore = $entityStore;
		$this->resourceListNodeParser = $resourceListNodeParser;
	}

	/**
	 * @see AbstractNode::isSimplifierFor
	 */
	public function isSimplifierFor(AbstractNode $node) {
		return $node instanceof IntersectionNode;
	}

	/**
	 * @see NodeSimplifier::doSimplification
	 */
	public function simplify(AbstractNode $node) {
		if(!$this->isSimplifierFor($node)) {
			throw new InvalidArgumentException('IntersectionWithFilterNodeSimplifier can only simplify IntersectionNode');
		}

		return $this->doSimplification($node);
	}

	private function doSimplification(IntersectionNode $node) {
		$triplesWithMissingSubjects = array();
		$otherOperands = array();

		foreach($node->getOperands() as $operand) {
			if($this->isTripleWithMissingSubject($operand)) {
				$triplesWithMissingSubjects[] = $operand;
			} else {
				$otherOperands[] = $operand;
			}
		}

		if(empty($otherOperands) || empty($triplesWithMissingSubjects)) {
			return $node; //case of the MissingSubjectNodeSimplifier
		}

		$baseList = $this->nodeSimplifierFactory->newNodeSimplifier()->simplify(new IntersectionNode($otherOperands));

		if(!($baseList instanceof ResourceListNode)) {
			$triplesWithMissingSubjects[] = $baseList;
			return new IntersectionNode($triplesWithMissingSubjects);
		}
		$baseList = $this->resourceListNodeParser->parse($baseList, 'wikibase-item');

		foreach($triplesWithMissingSubjects as $tripleWithMissingSubject) {
			$baseList = $this->applyTripleAsFilter($baseList, $tripleWithMissingSubject);
		}

		return $baseList;
	}

	private function isTripleWithMissingSubject(AbstractNode $node) {
		return $node instanceof TripleNode &&
			$node->getSubject() instanceof MissingNode &&
			$node->getPredicate() instanceof ResourceListNode &&
			$node->getObject() instanceof ResourceListNode;
	}

	private function applyTripleAsFilter(ResourceListNode $baseList, TripleNode $triple) {
		$possibleSnaks = $this->buildPossibleSnaks($triple);
		$filteredList = array();

		foreach($baseList as $resource) {
			if($this->isOneOfSnakInItem($resource->getDataValue()->getEntityId(), $possibleSnaks)) {
				$filteredList[] = $resource;
			}
		}

		return new ResourceListNode($filteredList);
	}

	private function isOneOfSnakInItem(ItemId $itemId, SnakList $snaks) {
		$item = $this->entityStore->getItemLookup()->getItemForId($itemId);

		/** @var Statement $statement */
		foreach($item->getStatements() as $statement) {
			if($snaks->hasSnak($statement->getMainSnak())) {
				return true;
			}
		}

		return false;
	}

	private function buildPossibleSnaks(TripleNode $triple) {
		$propertyNodes = $this->resourceListNodeParser->parse($triple->getPredicate(), 'wikibase-property');
		$possibleSnaks = new SnakList();

		foreach($this->bagsPropertiesPerType($propertyNodes) as $objectType => $propertyNodes) {
			$objectNodes = $this->resourceListNodeParser->parse($triple->getObject(), $objectType);

			foreach($propertyNodes as $property) {
				foreach($objectNodes as $object) {
					$possibleSnaks->addSnak(new PropertyValueSnak($property->getDataValue()->getEntityId(), $object->getDataValue()));
				}
			}
		}

		return $possibleSnaks;
	}

	private function bagsPropertiesPerType($propertyNodes) {
		$propertyNodesPerType = array();

		/** @var WikibaseResourceNode $propertyNode */
		foreach($propertyNodes as $propertyNode) {
			$objectType = $this->entityStore->getPropertyLookup()->getPropertyForId(
				$propertyNode->getDataValue()->getEntityId()
			)->getDataTypeId();
			$propertyNodesPerType[$objectType][] = $propertyNode;
		}

		return $propertyNodesPerType;
	}
}
