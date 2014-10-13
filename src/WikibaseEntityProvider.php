<?php

namespace PPP\Wikidata;

use OutOfRangeException;
use Wikibase\Api\Service\RevisionGetter;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * Provides Entity records from Wikibase API.
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseEntityProvider {

	/**
	 * @var RevisionGetter
	 */
	private $revisionGetter;

	/**
	 * @param RevisionGetter $revisionGetter
	 */
	public function __construct(RevisionGetter $revisionGetter) {
		$this->revisionGetter = $revisionGetter;
	}

	/**
	 * @param ItemId $itemId
	 * @return Item
	 * @throws OutOfRangeException
	 */
	public function getItem(ItemId $itemId) {
		return $this->getEntity($itemId);
	}

	/**
	 * @param PropertyId $propertyId
	 * @return Property
	 * @throws OutOfRangeException
	 */
	public function getProperty(PropertyId $propertyId) {
		return $this->getEntity($propertyId);
	}

	private function getEntity(EntityId $entityId) {
		$revision = $this->revisionGetter->getFromId($entityId);

		if($revision === false) {
			throw new OutOfRangeException('The entity ' . $entityId->getSerialization(). ' does not exists');
		}

		return $revision->getContent()->getNativeData();
	}
}
