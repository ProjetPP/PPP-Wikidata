<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd;

use DataValues\UnknownValue;
use InvalidArgumentException;
use stdClass;
use ValueFormatters\ValueFormatterBase;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdUnknownFormatter extends ValueFormatterBase implements JsonLdDataValueFormatter {

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof UnknownValue)) {
			throw new InvalidArgumentException('$value is not a UnknownValue.');
		}

		return $this->toJsonLd($value);
	}

	private function toJsonLd(UnknownValue $value) {
		$literal = new stdClass();
		$literal->{'@value'} = strval($value->getValue());
		return $literal;
	}
}
