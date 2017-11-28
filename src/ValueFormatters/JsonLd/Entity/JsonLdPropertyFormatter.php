<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd\Entity;

use InvalidArgumentException;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;
use ValueFormatters\ValueFormatterBase;
use Wikibase\DataModel\Entity\Property;

/**
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class JsonLdPropertyFormatter extends ValueFormatterBase {

	/**
	 * @var ValueFormatter
	 */
	private $entityFormatter;

	/**
	 * @param ValueFormatter $entityFormatter
	 * @param FormatterOptions $options
	 */
	public function __construct(ValueFormatter $entityFormatter, FormatterOptions $options) {
		$this->entityFormatter = $entityFormatter;

		parent::__construct($options);
	}

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof Property)) {
			throw new InvalidArgumentException('$value is not an Property.');
		}

		return $this->toJsonLd($value);
	}

	private function toJsonLd(Property $item) {
		$resource = $this->entityFormatter->format($item);
		$resource->{'@type'} = 'Property';
		return $resource;
	}
}
