<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\UnknownValue;
use InvalidArgumentException;
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

		return new StringResourceNode(strval($value->getValue()));
	}
}
