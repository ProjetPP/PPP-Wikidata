<?php

namespace PPP\Wikidata\TreeSimplifier;

use DataValues\DataValue;
use InvalidArgumentException;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\IntersectionNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\OperatorNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\TripleNode;
use PPP\DataModel\UnionNode;
use PPP\Module\TreeSimplifier\NodeSimplifier;
use PPP\Module\TreeSimplifier\NodeSimplifierException;
use PPP\Module\TreeSimplifier\NodeSimplifierFactory;
use PPP\Wikidata\ValueParsers\ResourceListNodeParser;
use PPP\Wikidata\WikibaseEntityProvider;
use PPP\Wikidata\WikibaseResourceNode;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\PropertyId;
use WikidataQueryApi\Query\AbstractQuery;
use WikidataQueryApi\Query\AndQuery;
use WikidataQueryApi\Query\AroundQuery;
use WikidataQueryApi\Query\BetweenQuery;
use WikidataQueryApi\Query\ClaimQuery;
use WikidataQueryApi\Query\OrQuery;
use WikidataQueryApi\Query\QuantityQuery;
use WikidataQueryApi\Query\StringQuery;
use WikidataQueryApi\Services\SimpleQueryService;

/**
 * Simplifies a triple node when the subject is missing.
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class MissingSubjectTripleNodeSimplifier implements NodeSimplifier {

	/**
	 * @var NodeSimplifierFactory
	 */
	private $nodeSimplifierFactory;

	/**
	 * @var SimpleQueryService
	 */
	private $simpleQueryService;

	/**
	 * @var WikibaseEntityProvider
	 */
	private $entityProvider;

	/**
	 * @var ResourceListNodeParser
	 */
	private $resourceListNodeParser;

	/**
	 * @param NodeSimplifierFactory $nodeSimplifierFactory
	 * @param SimpleQueryService $simpleQueryService
	 * @param WikibaseEntityProvider $entityProvider
	 * @param ResourceListNodeParser $resourceListNodeParser
	 */
	public function __construct(NodeSimplifierFactory $nodeSimplifierFactory, SimpleQueryService $simpleQueryService, WikibaseEntityProvider $entityProvider, ResourceListNodeParser $resourceListNodeParser) {
		$this->nodeSimplifierFactory = $nodeSimplifierFactory;
		$this->simpleQueryService = $simpleQueryService;
		$this->entityProvider = $entityProvider;
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

		$entityIds = $this->simpleQueryService->doQuery($query);

		$this->entityProvider->loadEntities($entityIds);

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
			return new OrQuery($queries);
		} elseif($operatorNode instanceof IntersectionNode) {
			return new AndQuery($queries);
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
				foreach($objectNodes as $objectNode) {
					$queryParameters[] = $this->buildQueryForObject($propertyNode, $objectNode);
				}
			}
		}

		return new OrQuery($queryParameters);
	}

	private function bagsPropertiesPerType($propertyNodes) {
		$propertyNodesPerType = array();

		/** @var WikibaseResourceNode $propertyNode */
		foreach($propertyNodes as $propertyNode) {
			$objectType = $this->entityProvider->getProperty($propertyNode->getDataValue()->getEntityId())->getDataTypeId();
			$propertyNodesPerType[$objectType][] = $propertyNode;
		}

		return $propertyNodesPerType;
	}

	private function buildQueryForObject(WikibaseResourceNode $predicate, WikibaseResourceNode $object) {
		return $this->buildQueryForValue(
			$predicate->getDataValue()->getEntityId(),
			$object->getDataValue()
		);
	}

	/**
	 * @return AbstractQuery
	 */
	private function buildQueryForValue(PropertyId $propertyId, DataValue $value) {
		switch($value->getType()) {
			case 'globecoordinate':
				return new AroundQuery(
					$propertyId,
					$value->getLatLong(),
					$this->getRadiusFromGeoCoordinatesPrecision($value->getPrecision())
				);
			case 'quantity':
				return new QuantityQuery($propertyId, $value->getAmount());
			case 'string':
				return new StringQuery($propertyId, $value);
			case 'time':
				return new BetweenQuery($propertyId, $value, $value);
			case 'wikibase-entityid':
				return new ClaimQuery($propertyId, $value->getEntityId());
			default:
				throw new NodeSimplifierException('The data type ' . $value->getType() . ' is not supported.');
		}
	}

	private function getRadiusFromGeoCoordinatesPrecision($precision) {
		if($precision <= 0) {
			$precision = 1 / 3600;
		}

		return $precision * 100;
	}

	private function formatQueryResult(array $subjectIds) {
		$nodes = array();

		foreach($subjectIds as $subjectId) {
			$nodes[] = new WikibaseResourceNode('', new EntityIdValue($subjectId));
		}

		return new ResourceListNode($nodes);
	}
}
