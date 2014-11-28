<?php

namespace PPP\Wikidata\TreeSimplifier;

use DataValues\DataValue;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\ResourceNode;
use PPP\DataModel\TripleNode;
use PPP\Module\TreeSimplifier\AbstractTripleNodeSimplifier;
use PPP\Module\TreeSimplifier\NodeSimplifierException;
use PPP\Module\TreeSimplifier\NodeSimplifierFactory;
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
class MissingSubjectTripleNodeSimplifier extends AbstractTripleNodeSimplifier {

	/**
	 * @var SimpleQueryService
	 */
	private $simpleQueryService;

	/**
	 * @param SimpleQueryService $simpleQueryService
	 */
	public function __construct(NodeSimplifierFactory $simplifierFactory, SimpleQueryService $simpleQueryService) {
		$this->simpleQueryService = $simpleQueryService;

		parent::__construct($simplifierFactory);
	}

	/**
	 * @see AbstractNode::isSimplifierFor
	 */
	public function isSimplifierFor(AbstractNode $node) {
		return $node instanceof TripleNode && $node->getSubject() instanceof MissingNode;
	}

	/**
	 * @see AbstractTripleNodeSimplifier::doSimplification
	 * @param MissingNode $subjects
	 * @param ResourceListNode $predicates
	 * @param ResourceListNode $objects
	 */
	protected function doSimplification(AbstractNode $subjects, AbstractNode $predicates, AbstractNode $objects) {
		$queryResult = array();

		foreach($predicates as $predicate) {
			foreach($objects as $object) {
				$queryResult = array_merge(
					$queryResult,
					$this->getQueryResultsForObject($predicate, $object)
				);
			}
		}

		return $this->formatQueryResult($queryResult);
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
