<?php

namespace PPP\Wikidata\ValueParsers;

use DataValues\MonolingualTextValue;
use ValueParsers\StringValueParser;
use ValueParsers\ValueParser;

/**
 * Parse string value.
 *
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class MonolingualTextParser extends StringValueParser {

	const FORMAT_NAME = 'monolingualtext';

	protected function stringParse($value) {
		return new MonolingualTextValue($this->getOption(ValueParser::OPT_LANG), $value);
	}
}
