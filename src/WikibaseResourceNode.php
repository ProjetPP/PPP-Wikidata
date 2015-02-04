<?php

namespace PPP\Wikidata;

use DataValues\DataValue;
use PPP\DataModel\ResourceNode;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * Annotates intermediate representation with Wikibase
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseResourceNode extends ResourceNode {

	/**
	 * @var DataValue
	 */
	private $dataValue;

	/**
	 * @var EntityId|null the subject from which the value is retrieved
	 */
	private $fromSubject;

	/**
	 * @var PropertyId|null the predicate from which the value is retrieved
	 */
	private $fromPredicate;

	/**
	 * @param string $value
	 * @param DataValue $dataValue
	 * @param EntityId $fromSubject
	 * @param PropertyId $fromPredicate
	 */
	public function __construct($value, DataValue $dataValue, EntityId $fromSubject = null, PropertyId $fromPredicate = null) {
		$this->dataValue = $dataValue;
		$this->fromSubject = $fromSubject;
		$this->fromPredicate = $fromPredicate;

		parent::__construct($value);
	}

	/**
	 * @return DataValue
	 */
	public function getDataValue() {
		return $this->dataValue;
	}

	/**
	 * @return EntityId|null
	 */
	public function getFromSubject() {
		return $this->fromSubject;
	}

	/**
	 * @return PropertyId|null
	 */
	public function getFromPredicate() {
		return $this->fromPredicate;
	}

	/**
	 * @see AbstractNode::equals
	 */
	public function equals($target) {
		return $target instanceof self &&
			$this->dataValue->equals($target->dataValue);
	}

	/**
	 * @see WikibaseResourceNode::equals
	 */
	public function getValueType() {
		return 'wikidata-datavalue';
	}
}
