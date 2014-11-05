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
		return $this->entityProvider->getProperty($propertyId)->getDataTypeId();
	}
}
