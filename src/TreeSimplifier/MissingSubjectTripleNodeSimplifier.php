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
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\TripleNode;
use PPP\DataModel\UnionNode;
use PPP\Module\TreeSimplifier\NodeSimplifier;
use PPP\Module\TreeSimplifier\NodeSimplifierException;
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
		try {
			$query = $this->buildQueryForNode($node);
		} catch(EmptyQueryException $e) {
			return new ResourceListNode();
		}

		$entityIds = $this->entityStore->getItemIdForQueryLookup()->getItemIdsForQuery($query, new QueryOptions(self::QUERY_LIMIT, 0));

		return $this->formatQueryResult($entityIds);
	}

	private function buildQueryForNode(AbstractNode $node) {
		if($node instanceof UnionNode) {
			return $this->buildQueryForUnion($node);
		} else if($node instanceof IntersectionNode) {
			return $this->buildQueryForIntersection($node);
		} else if($node instanceof TripleNode) {
			return $this->buildQueryForTriple($node);
		} else {
			throw new InvalidArgumentException('Unsupported Node');
		}
	}

	private function buildQueryForUnion(UnionNode $unionNode) {
		$queries = array();

		foreach($unionNode->getOperands() as $operandNode) {
			try {
				$queries[] = $this->buildQueryForNode($operandNode);
			} catch(EmptyQueryException $e) {
				//May be ignored: we are in a union
			}
		}

		switch(count($queries)) {
			case 0:
				throw new EmptyQueryException();
			case 1:
				return reset($queries);
			default:
				return new Disjunction($queries);
		}
	}

	private function buildQueryForIntersection(IntersectionNode $intersectionNode) {
		$queries = array();

		foreach($intersectionNode->getOperands() as $operandNode) {
			$queries[] = $this->buildQueryForNode($operandNode);
		}

		switch(count($queries)) {
			case 1:
				return reset($queries);
			default:
				return new Conjunction($queries);
		}
	}

	private function buildQueryForTriple(TripleNode $triple) {
		if(!($triple->getSubject()->equals(new MissingNode()))) {
			throw new InvalidArgumentException('Triple whose subject is not missing given.');
		}

		$simplifier = $this->nodeSimplifierFactory->newNodeSimplifier();
		$predicate = $simplifier->simplify($triple->getPredicate());
		$object = $simplifier->simplify($triple->getObject());
		if(!($predicate instanceof ResourceListNode && $object instanceof ResourceListNode)) {
			throw new NodeSimplifierException('Invalid triple');
		}

		$propertyNodes = $this->resourceListNodeParser->parse($predicate, 'wikibase-property');
		$queryParameters = array();

		foreach($this->bagsPropertiesPerType($propertyNodes) as $objectType => $propertyNodes) {
			$objectNodes = $this->resourceListNodeParser->parse($object, $objectType);

			foreach($propertyNodes as $propertyNode) {
				try {
					$queryParameters[] = $this->buildQueryForProperty($propertyNode, $objectNodes);
				} catch(EmptyQueryException $e) {
					//May be ignored: we are in a union
				}
			}
		}

		switch(count($queryParameters)) {
			case 0:
				throw new EmptyQueryException();
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
			case 0:
				throw new EmptyQueryException();
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
