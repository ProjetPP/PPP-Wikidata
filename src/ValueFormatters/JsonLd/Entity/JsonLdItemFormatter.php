<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd\Entity;

use InvalidArgumentException;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;
use ValueFormatters\ValueFormatterBase;
use Wikibase\DataModel\Entity\Item;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 * @todo output sitelinks
 */
class JsonLdItemFormatter extends ValueFormatterBase {

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
		if(!($value instanceof Item)) {
			throw new InvalidArgumentException('$value is not an Item.');
		}

		return $this->toJsonLd($value);
	}

	private function toJsonLd(Item $item) {
		$resource = $this->entityFormatter->format($item);
		return $resource;
	}
}
