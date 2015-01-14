<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\DataValue;
use InvalidArgumentException;
use PPP\DataModel\ResourceNode;
use PPP\Wikidata\WikibaseResourceNode;
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
class DispatchingWikibaseResourceNodeFormatter extends ValueFormatterBase implements WikibaseResourceNodeFormatter {

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
	 * @param DataValue $value
	 * @return ResourceNode
	 */
	public function format($value) {
		if(!($value instanceof WikibaseResourceNode)) {
			throw new InvalidArgumentException('$value should be a WikibaseResourceNode');
		}

		$type = $value->getDataValue()->getType();

		if(!array_key_exists($type, $this->formatters)) {
			throw new FormattingException('Unknown value type: ' . $type);
		}

		return $this->doFormatting($this->formatters[$type], $value);
	}

	private function doFormatting(ValueFormatter $formatter, WikibaseResourceNode $value) {
		if($formatter instanceof DataValueFormatter) {
			return $formatter->format($value->getDataValue());
		} else if($formatter instanceof WikibaseResourceNodeFormatter) {
			return $formatter->format($value);
		} else {
			throw new InvalidArgumentException('Formatter with unknown behaviour');
		}
	}
}
