<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd;

use DataValues\StringValue;
use InvalidArgumentException;
use stdClass;
use ValueFormatters\ValueFormatterBase;

/**
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class JsonLdStringFormatter extends ValueFormatterBase implements JsonLdDataValueFormatter {

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof StringValue)) {
			throw new InvalidArgumentException('$value is not a StringValue.');
		}

		return $this->toJsonLd($value);
	}

	private function toJsonLd(StringValue $value) {
		$literal = new stdClass();
		$literal->{'@value'} = $value->getValue();
		return $literal;
	}
}
