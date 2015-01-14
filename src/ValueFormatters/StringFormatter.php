<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\StringValue;
use InvalidArgumentException;
use PPP\DataModel\StringResourceNode;
use ValueFormatters\ValueFormatterBase;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class StringFormatter extends ValueFormatterBase implements DataValueFormatter {

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof StringValue)) {
			throw new InvalidArgumentException('$value is not a StringValue.');
		}

		return new StringResourceNode($value->getValue());
	}
}
