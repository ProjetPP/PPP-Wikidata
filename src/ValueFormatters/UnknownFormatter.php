<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\UnknownValue;
use InvalidArgumentException;
use PPP\DataModel\ResourceNode;
use PPP\DataModel\StringResourceNode;
use ValueFormatters\ValueFormatterBase;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class UnknownFormatter extends ValueFormatterBase {

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof UnknownValue)) {
			throw new InvalidArgumentException('$value is not a UnknownValue.');
		}

		if($value->getValue() instanceof ResourceNode) {
			return $value->getValue();
		} else {
			return new StringResourceNode(strval($value->getValue()));
		}
	}
}
