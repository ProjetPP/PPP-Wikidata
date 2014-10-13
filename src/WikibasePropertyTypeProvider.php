<?php

namespace PPP\Wikidata;

use Wikibase\DataModel\Entity\PropertyId;

/**
 * Provide data type of values for Wikibase properties.
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibasePropertyTypeProvider {

	/**
	 * @var WikibaseEntityProvider
	 */
	private $entityProvider;

	/**
	 * @var string[]
	 */
	private $propertyTypeCache = array();

	/**
	 * @param WikibaseEntityProvider $entityProvider
	 */
	public function __construct(WikibaseEntityProvider $entityProvider) {
		$this->entityProvider = $entityProvider;
	}

	/**
	 * @param PropertyId $propertyId
	 * @return string
	 */
	public function getTypeForProperty(PropertyId $propertyId) {
		if(!array_key_exists($propertyId->getNumericId(), $this->propertyTypeCache)) {
			$this->propertyTypeCache[$propertyId->getNumericId()] = $this->retrievePropertyType($propertyId);
		}

		return $this->propertyTypeCache[$propertyId->getNumericId()];
	}

	private function retrievePropertyType(PropertyId $propertyId) {
		return $this->entityProvider->getProperty($propertyId)->getDataTypeId();
	}
}
