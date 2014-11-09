<?php

namespace PPP\Wikidata;

use DataValues\DataValue;
use PPP\DataModel\ResourceNode;

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
	 * @param string $value
	 * @param DataValue $dataValue
	 */
	public function __construct($value, DataValue $dataValue) {
		$this->dataValue = $dataValue;
		parent::__construct($value);
	}

	/**
	 * @return DataValue
	 */
	public function getDataValue() {
		return $this->dataValue;
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
