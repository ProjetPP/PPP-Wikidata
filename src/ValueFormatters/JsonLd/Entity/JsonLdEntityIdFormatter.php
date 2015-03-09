<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd\Entity;

use InvalidArgumentException;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdDataValueFormatter;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;
use ValueFormatters\ValueFormatterBase;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\ItemLookup;
use Wikibase\DataModel\Entity\ItemNotFoundException;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\PropertyLookup;
use Wikibase\DataModel\Entity\PropertyNotFoundException;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdEntityIdFormatter extends ValueFormatterBase implements JsonLdDataValueFormatter {

	/**
	 * @var ItemLookup
	 */
	private $itemLookup;

	/**
	 * @var JsonLdItemFormatter
	 */
	private $itemFormatter;

	/**
	 * @var PropertyLookup
	 */
	private $propertyLookup;

	/**
	 * @var JsonLdPropertyFormatter
	 */
	private $propertyFormatter;

	/**
	 * @param ItemLookup $itemLookup
	 * @param ValueFormatter $itemFormatter
	 * @param PropertyLookup $propertyLookup
	 * @param ValueFormatter $propertyFormatter
	 * @param FormatterOptions $options
	 */
	public function __construct(
		ItemLookup $itemLookup,
		ValueFormatter $itemFormatter,
		PropertyLookup $propertyLookup,
		ValueFormatter $propertyFormatter,
		FormatterOptions $options
	) {
		$this->itemLookup = $itemLookup;
		$this->itemFormatter = $itemFormatter;
		$this->propertyLookup = $propertyLookup;
		$this->propertyFormatter = $propertyFormatter;

		parent::__construct($options);
	}

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof EntityIdValue)) {
			throw new InvalidArgumentException('$value is not a EntityIdValue.');
		}

		return $this->toJsonLd($value);
	}

	private function toJsonLd(EntityIdValue $value) {
		$entityId = $value->getEntityId();

		if($entityId instanceof ItemId) {
			return $this->itemIdToJsonLd($entityId);
		} elseif($entityId instanceof PropertyId) {
			return $this->propertyIdToJsonLd($entityId);
		}

		throw new InvalidArgumentException('Unsupported entity type: ' . $entityId->getEntityType());
	}

	private function itemIdToJsonLd(ItemId $itemId) {
		try {
			$item = $this->itemLookup->getItemForId($itemId);
		} catch(ItemNotFoundException $e) {
			$item = new Item($itemId);
		}

		return $this->itemFormatter->format($item);
	}

	private function propertyIdToJsonLd(PropertyId $propertyId) {
		try {
			$property = $this->propertyLookup->getPropertyForId($propertyId);
		} catch(PropertyNotFoundException $e) {
			$property = new Property($propertyId, null, '');
		}

		return $this->propertyFormatter->format($property);
	}
}
