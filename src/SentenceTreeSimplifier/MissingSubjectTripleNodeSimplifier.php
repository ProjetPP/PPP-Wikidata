<?php

namespace PPP\Wikidata\SentenceTreeSimplifier;

use DataValues\DataValue;
use InvalidArgumentException;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\TripleNode;
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
 */
class MissingSubjectTripleNodeSimplifier implements NodeSimplifier {

	/**
	 * @var SimpleQueryService
	 */
	private $simpleQueryService;

	/**
	 * @param SimpleQueryService $simpleQueryService
	 */
	public function __construct(SimpleQueryService $simpleQueryService) {
		$this->simpleQueryService = $simpleQueryService;
	}

	/**
	 * @see AbstractNode::isSimplifierFor
	 */
	public function isSimplifierFor(AbstractNode $node) {
		return $node instanceof TripleNode &&
			$node->getSubject() instanceof MissingNode &&
			$node->getPredicate() instanceof WikibaseResourceNode &&
			$node->getObject() instanceof WikibaseResourceNode;
	}

	/**
	 * @see AbstractNode::simplify
	 */
	public function simplify(AbstractNode $node) {
		/** @var TripleNode $node */
		if(!$this->isSimplifierFor($node)) {
			throw new InvalidArgumentException('MissingSubjectTripleNodeSimplifier can not simplify this node!');
		}

		/** @var PropertyId $propertyId */
		$propertyId = $node->getPredicate()->getDataValue()->getEntityId();
		/** @var DataValue $value */
		$value = $node->getObject()->getDataValue();

		return $this->formatQueryResult($this->simpleQueryService->doQuery(
			$this->buildQueryForValue($propertyId, $value)
		));
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
				throw new SimplifierException('The data type ' . $value->getType() . ' is not supported.');
		}
	}

	private function formatQueryResult(array $subjectIds) {
		$nodes = array();

		foreach($subjectIds as $subjectId) {
			$nodes[] = new WikibaseResourceNode('', new EntityIdValue($subjectId));
		}

		return $nodes;
	}
}
