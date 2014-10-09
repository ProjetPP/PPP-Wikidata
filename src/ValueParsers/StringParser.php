<?php

namespace PPP\Wikidata\ValueParsers;

use DataValues\StringValue;
use ValueParsers\StringValueParser;

/**
 * Parse string value.
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class StringParser extends StringValueParser {

	const FORMAT_NAME = 'string';

	protected function stringParse($value) {
		return new StringValue($value);
	}
}
