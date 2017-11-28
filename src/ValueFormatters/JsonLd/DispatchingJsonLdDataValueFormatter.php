<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd;

use DataValues\DataValue;
use InvalidArgumentException;
use ValueFormatters\FormatterOptions;
use ValueFormatters\FormattingException;
use ValueFormatters\ValueFormatterBase;

/**
 * Choose the right formatter for the given type and return the formatted value.
 *
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class DispatchingJsonLdDataValueFormatter extends ValueFormatterBase implements JsonLdDataValueFormatter {

	/**
	 * @var JsonLdDataValueFormatter[]
	 */
	public $formatters;

	/**
	 * @param JsonLdDataValueFormatter[] $formatters
	 * @param FormatterOptions $options
	 */
	public function __construct(array $formatters, FormatterOptions $options) {
		$this->formatters = $formatters;

		parent::__construct($options);
	}

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof DataValue)) {
			throw new InvalidArgumentException('$value should be a DataValue');
		}

		$type = $value->getType();

		if(!array_key_exists($type, $this->formatters)) {
			throw new FormattingException(
				$type . ' is not one of the type supported by the value formatter (' . implode(', ', array_keys($this->formatters)) . ')'
			);
		}

		return $this->formatters[$type]->format($value);
	}
}
