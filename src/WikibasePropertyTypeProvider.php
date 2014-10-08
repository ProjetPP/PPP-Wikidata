<?php

namespace PPP\Wikidata;

use OutOfRangeException;
use Wikibase\Api\Service\RevisionGetter;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * Provide data type of values for Wikibase properties.
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibasePropertyTypeProvider {

	/**
	 * @var RevisionGetter
	 */
	private $revisionGetter;

	/**
	 * @var string[]
	 */
	private $propertyTypeCache = array();

	/**
	 * @param RevisionGetter $revisionGetter
	 */
	public function __construct(RevisionGetter $revisionGetter) {
		$this->revisionGetter = $revisionGetter;
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
		$propertyRevision = $this->revisionGetter->getFromId($propertyId);
		if($propertyRevision === false) {
			throw new OutOfRangeException('The property ' . $propertyId->getPrefixedId() . ' does not exists');
		}

		/** @var Property $property */
		$property = $propertyRevision->getContent()->getNativeData();
		return $property->getDataTypeId();
	}
}
