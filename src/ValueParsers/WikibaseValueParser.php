<?php

namespace PPP\Wikidata\ValueParsers;

use DataValues\DataValue;
use ValueParsers\ParseException;
use ValueParsers\ValueParser;

/**
 * Choose the right parser for the given type and return the parsed value.
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseValueParser {

	/**
	 * @var ValueParser[]
	 */
	public $parsers;

	/**
	 * @param ValueParser[] $parsers
	 */
	public function __construct(array $parsers) {
		$this->parsers = $parsers;
	}

	/**
	 * @param string $value
	 * @param string $type
	 * @return DataValue[]
	 */
	public function parse($value, $type) {
		if(!array_key_exists($type, $this->parsers)) {
			throw new ParseException('Unknown value type', $value, $type);
		}

		$result = $this->parsers[$type]->parse($value);

		if(!is_array($result)) {
			return array($result);
		}

		return $result;
	}
}
