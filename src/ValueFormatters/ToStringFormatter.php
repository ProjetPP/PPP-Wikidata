<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\DataValue;
use InvalidArgumentException;
use PPP\DataModel\StringResourceNode;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;
use ValueFormatters\ValueFormatterBase;

/**
 * Use an other formatter to build a StringResourceNode
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 *
 * @todo write nice formatters for each type
 */
class ToStringFormatter extends ValueFormatterBase {

	/**
	 * @var ValueFormatter
	 */
	private $formatter;

	/**
	 * @param ValueFormatter $formatter
	 */
	public function __construct(ValueFormatter $formatter) {
		$this->formatter = $formatter;

		parent::__construct(new FormatterOptions());
	}

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof DataValue)) {
			throw new InvalidArgumentException('$value is not a DataValue.');
		}

		return new StringResourceNode($this->formatter->format($value));
	}
}
