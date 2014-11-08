<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\MonolingualTextValue;
use InvalidArgumentException;
use PPP\DataModel\StringResourceNode;
use ValueFormatters\ValueFormatterBase;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class MonolingualTextFormatter extends ValueFormatterBase {

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof MonolingualTextValue)) {
			throw new InvalidArgumentException('DataValue is not a MonolingualTextValue.');
		}

		return new StringResourceNode($value->getText(), $value->getLanguageCode());
	}
}
