<?php

namespace PPP\Wikidata\TreeSimplifier;

use Ask\Language\Description\Conjunction;
use Ask\Language\Description\Disjunction;
use Ask\Language\Description\SomeProperty;
use Ask\Language\Description\ValueDescription;
use Ask\Language\Option\QueryOptions;
use InvalidArgumentException;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\IntersectionNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\OperatorNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\TripleNode;
use PPP\DataModel\UnionNode;
use PPP\Module\TreeSimplifier\NodeSimplifier;
use PPP\Module\TreeSimplifier\NodeSimplifierFactory;
use PPP\Wikidata\ValueParsers\ResourceListNodeParser;
use PPP\Wikidata\WikibaseResourceNode;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\EntityStore\EntityStore;

/**
 * Simplifies a triple node when the subject is missing.
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class MissingSubjectTripleNodeSimplifier implements NodeSimplifier {

	const QUERY_LIMIT = 50;

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
		return $this->isTripleWithMissingSubject($node) || $this->isTripleWithMissingSubjectOperator($node);
	}

	private function isTripleWithMissingSubject(AbstractNode $node) {
		return $node instanceof TripleNode &&
			$node->getSubject() instanceof MissingNode;
	}

	private function isTripleWithMissingSubjectOperator(AbstractNode $node) {
		if(!($node instanceof IntersectionNode || $node instanceof UnionNode) ) {
			return false;
		}

		foreach($node->getOperands() as $operand) {
			if(!$this->isSimplifierFor($operand)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @see NodeSimplifier::doSimplification
	 */
	public function simplify(AbstractNode $node) {
		if(!$this->isSimplifierFor($node)) {
			throw new InvalidArgumentException('MissingSubjectTripleNodeSimplifier can only simplify union and intersection of TripleNodes with missing subject');
		}

		return $this->doSimplification($node);
	}

	private function doSimplification(AbstractNode $node) {
		try {
			$query = $this->buildQueryForNode($node);
		} catch(InvalidArgumentException $e) {
			return $node;
		}

		$entityIds = $this->entityStore->getItemIdForQueryLookup()->getItemIdsForQuery($query, new QueryOptions(self::QUERY_LIMIT, 0));

		return $this->formatQueryResult($entityIds);
	}

	private function buildQueryForNode(AbstractNode $node) {
		if($node instanceof TripleNode) {
			return $this->buildQueryForTriple($node);
		} else if($node instanceof OperatorNode) {
			return $this->buildQueryForOperator($node);
		} else {
			throw new InvalidArgumentException('Unsupported Node');
		}
	}

	private function buildQueryForOperator(OperatorNode $operatorNode) {
		$queries = array();

		foreach($operatorNode->getOperands() as $operandNode) {
			$queries[] = $this->buildQueryForNode($operandNode);
		}

		if($operatorNode instanceof UnionNode) {
			return new Disjunction($queries);
		} elseif($operatorNode instanceof IntersectionNode) {
			return new Conjunction($queries);
		} else {
			throw new InvalidArgumentException('Unsupported OperatorNode');
		}
	}

	private function buildQueryForTriple(TripleNode $triple) {
		$simplifier = $this->nodeSimplifierFactory->newNodeSimplifier();
		$predicate = $simplifier->simplify($triple->getPredicate());
		$object = $simplifier->simplify($triple->getObject());
		if(!($predicate instanceof ResourceListNode && $object instanceof ResourceListNode)) {
			throw new InvalidArgumentException('Invalid triple');
		}

		$propertyNodes = $this->resourceListNodeParser->parse($predicate, 'wikibase-property');
		$queryParameters = array();

		foreach($this->bagsPropertiesPerType($propertyNodes) as $objectType => $propertyNodes) {
			$objectNodes = $this->resourceListNodeParser->parse($object, $objectType);

			foreach($propertyNodes as $propertyNode) {
				$queryParameters[] = $this->buildQueryForProperty($propertyNode, $objectNodes);
			}
		}

		switch(count($queryParameters)) {
			case 1:
				return reset($queryParameters);
			default:
				return new Disjunction($queryParameters);
		}
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

	private function buildQueryForProperty(WikibaseResourceNode $predicate, ResourceListNode $objectList) {
		return new SomeProperty(
			new EntityIdValue($predicate->getDataValue()->getEntityId()),
			$this->buildValueDescriptionsForObjects($objectList)
		);
	}

	private function buildValueDescriptionsForObjects(ResourceListNode $objectList) {
		$valueDescriptions = array();

		/** @var WikibaseResourceNode $object */
		foreach($objectList as $object) {
			$valueDescriptions[] = new ValueDescription($object->getDataValue());
		}

		switch(count($valueDescriptions)) {
			case 1:
				return reset($valueDescriptions);
			default:
				return new Disjunction($valueDescriptions);
		}
	}

	private function formatQueryResult(array $subjectIds) {
		$nodes = array();

		foreach($subjectIds as $subjectId) {
			$nodes[] = new WikibaseResourceNode('', new EntityIdValue($subjectId));
		}

		return new ResourceListNode($nodes);
	}
}
