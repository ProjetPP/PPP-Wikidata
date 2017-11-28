<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd;

use DataValues\DecimalValue;
use InvalidArgumentException;
use stdClass;
use ValueFormatters\ValueFormatterBase;

/**
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class JsonLdDecimalFormatter extends ValueFormatterBase implements JsonLdDataValueFormatter {

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof DecimalValue)) {
			throw new InvalidArgumentException('$value is not a DecimalValue.');
		}

		return $this->toJsonLd($value);
	}

	private function toJsonLd(DecimalValue $value) {
		$literal = new stdClass();

		if($value->getFractionalPart() === '') {
			$literal->{'@type'} = 'Integer';
			$literal->{'@value'} = $value->getIntegerPart();
		} else {
			$literal->{'@type'} = 'Float';
			$literal->{'@value'} = $value->getValueFloat();
		}

		return $literal;
	}
}
