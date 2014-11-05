<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\DataValue;
use InvalidArgumentException;
use ValueFormatters\FormatterOptions;
use ValueFormatters\FormattingException;
use ValueFormatters\ValueFormatter;
use ValueFormatters\ValueFormatterBase;

/**
 * Choose the right formatter for the given type and return the formatted value.
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseValueFormatter extends ValueFormatterBase {

	/**
	 * @var ValueFormatter[]
	 */
	public $formatters;

	/**
	 * @param ValueFormatter[] $formatters
	 */
	public function __construct(array $formatters) {
		$this->formatters = $formatters;
		parent::__construct(new FormatterOptions());
	}

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof DataValue)) {
			throw new InvalidArgumentException('$value should be a DataValue');
		}

		if(!array_key_exists($value->getType(), $this->formatters)) {
			throw new FormattingException('Unknown value type: ' . $value->getType());
		}

		return $this->formatters[$value->getType()]->format($value);
	}
}
