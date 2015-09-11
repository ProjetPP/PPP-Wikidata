<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd;

use DataValues\QuantityValue;
use InvalidArgumentException;
use stdClass;
use ValueFormatters\FormatterOptions;
use ValueFormatters\QuantityFormatter;
use ValueFormatters\ValueFormatterBase;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 * @todo support units
 */
class JsonLdQuantityFormatter extends ValueFormatterBase implements JsonLdDataValueFormatter {

	/**
	 * @var QuantityFormatter
	 */
	private $quantityFormatter;

	/**
	 * @var JsonLdDecimalFormatter
	 */
	private $decimalFormatter;

	/**
	 * @param QuantityFormatter $quantityFormatter
	 * @param JsonLdDecimalFormatter $decimalFormatter
	 * @param FormatterOptions|null $options
	 */
	public function __construct(
		QuantityFormatter $quantityFormatter,
		JsonLdDecimalFormatter $decimalFormatter,
		FormatterOptions $options = null
	) {
		$this->quantityFormatter = $quantityFormatter;
		$this->decimalFormatter = $decimalFormatter;

		parent::__construct($options);
	}

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof QuantityValue)) {
			throw new InvalidArgumentException('$value is not a QuantityValue.');
		}

		return $this->toJsonLd($value);
	}

	private function toJsonLd(QuantityValue $value) {
		$resource = new stdClass();
		$resource->{'@type'} = 'QuantitativeValue';
		$resource->name = $this->quantityFormatter->format($value);
		$resource->value = $this->decimalFormatter->format($value->getAmount());
		$resource->minValue = $this->decimalFormatter->format($value->getLowerBound());
		$resource->maxValue = $this->decimalFormatter->format($value->getUpperBound());

		if($value->getUnit() !== '1') {
			$resource->unitCode = $value->getUnit();
		}

		return $resource;
	}
}
