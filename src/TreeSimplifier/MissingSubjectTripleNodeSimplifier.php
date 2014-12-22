<?php

namespace PPP\Wikidata\TreeSimplifier;

use DataValues\DataValue;
use InvalidArgumentException;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\TripleNode;
use PPP\Module\TreeSimplifier\NodeSimplifier;
use PPP\Module\TreeSimplifier\NodeSimplifierException;
use PPP\Wikidata\ValueParsers\ResourceListNodeParser;
use PPP\Wikidata\WikibaseEntityProvider;
use PPP\Wikidata\WikibaseResourceNode;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\PropertyId;
use WikidataQueryApi\Query\AbstractQuery;
use WikidataQueryApi\Query\AroundQuery;
use WikidataQueryApi\Query\BetweenQuery;
use WikidataQueryApi\Query\ClaimQuery;
use WikidataQueryApi\Query\QuantityQuery;
use WikidataQueryApi\Query\StringQuery;
use WikidataQueryApi\Services\SimpleQueryService;

/**
 * Simplifies a triple node when the subject is missing.
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 * @todo do only one query with OR
 */
class MissingSubjectTripleNodeSimplifier implements NodeSimplifier {

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
	 * @param SimpleQueryService $simpleQueryService
	 * @param WikibaseEntityProvider $entityProvider
	 * @param ResourceListNodeParser $resourceListNodeParser
	 */
	public function __construct(SimpleQueryService $simpleQueryService, WikibaseEntityProvider $entityProvider, ResourceListNodeParser $resourceListNodeParser) {
		$this->simpleQueryService = $simpleQueryService;
		$this->entityProvider = $entityProvider;
		$this->resourceListNodeParser = $resourceListNodeParser;
	}

	/**
	 * @see AbstractNode::isSimplifierFor
	 */
	public function isSimplifierFor(AbstractNode $node) {
		return $node instanceof TripleNode &&
		$node->getSubject() instanceof MissingNode &&
		$node->getPredicate() instanceof ResourceListNode &&
		$node->getObject() instanceof ResourceListNode;
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
		$propertyNodes = $this->resourceListNodeParser->parse($node->getPredicate(), 'wikibase-property');
		$queryResult = array();

		foreach($this->bagsPropertiesPerType($propertyNodes) as $objectType => $propertyNodes) {
			$objectNodes = $this->resourceListNodeParser->parse($node->getObject(), $objectType);

			foreach($propertyNodes as $property) {
				foreach($objectNodes as $object) {
					$queryResult = array_merge(
						$queryResult,
						$this->getQueryResultsForObject($property, $object)
					);
				}
			}
		}

		return $this->formatQueryResult($queryResult);
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

	private function getQueryResultsForObject(WikibaseResourceNode $predicate, WikibaseResourceNode $object) {
		/** @var PropertyId $propertyId */
		$propertyId = $predicate->getDataValue()->getEntityId();
		/** @var DataValue $value */
		$value = $object->getDataValue();

		return $this->simpleQueryService->doQuery(
			$this->buildQueryForValue($propertyId, $value)
		);
	}

	/**
	 * @return AbstractQuery
	 */
	private function buildQueryForValue(PropertyId $propertyId, DataValue $value) {
		switch($value->getType()) {
			case 'globecoordinate':
				return new AroundQuery($propertyId, $value->getLatLong(), $value->getPrecision() * 100);
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

	private function formatQueryResult(array $subjectIds) {
		$nodes = array();

		foreach($subjectIds as $subjectId) {
			$nodes[] = new WikibaseResourceNode('', new EntityIdValue($subjectId));
		}

		return new ResourceListNode($nodes);
	}
}
