<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd;

use DataValues\MonolingualTextValue;
use InvalidArgumentException;
use stdClass;
use ValueFormatters\ValueFormatterBase;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdMonolingualTextFormatter extends ValueFormatterBase implements JsonLdDataValueFormatter {

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof MonolingualTextValue)) {
			throw new InvalidArgumentException('$value is not a MonolingualTextValue.');
		}

		return $this->toJsonLd($value);
	}

	private function toJsonLd(MonolingualTextValue $value) {
		$literal = new stdClass();
		$literal->{'@language'} = $value->getLanguageCode();
		$literal->{'@value'} = $value->getText();
		return $literal;
	}
}
