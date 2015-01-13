<?php

namespace PPP\Wikidata;

use OutOfBoundsException;
use PPP\Wikidata\Cache\WikibaseEntityCache;
use Wikibase\Api\Service\RevisionsGetter;
use Wikibase\DataModel\Entity\EntityDocument;
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
 * @todo loadEntities asynchronous?
 */
class WikibaseEntityProvider {

	/**
	 * @var RevisionsGetter
	 */
	private $revisionsGetter;

	/**
	 * @var WikibaseEntityCache
	 */
	private $cache;

	/**
	 * @param RevisionsGetter $revisionGetter
	 * @param WikibaseEntityCache $cache
	 */
	public function __construct(RevisionsGetter $revisionGetter, WikibaseEntityCache $cache) {
		$this->revisionsGetter = $revisionGetter;
		$this->cache = $cache;
	}

	/**
	 * Makes sure that entities are loaded into the cache
	 *
	 * @param EntityId[] $entityIds
	 */
	public function loadEntities(array $entityIds) {
		$entitiesToRetrieve = array();

		foreach($entityIds as $entityId) {
			if(!$this->cache->contains($entityId)) {
				$entitiesToRetrieve[] = $entityId;
			}
		}

		if(empty($entitiesToRetrieve)) {
			return;
		}
		$entities = $this->getEntitiesFromApi($entitiesToRetrieve);

		foreach($entities as $entity) {
			$this->cache->save($entity);
		}
	}

	/**
	 * @param ItemId $itemId
	 * @return Item
	 * @throws OutOfBoundsException
	 */
	public function getItem(ItemId $itemId) {
		return $this->getEntityDocument($itemId);
	}

	/**
	 * @param PropertyId $propertyId
	 * @return Property
	 * @throws OutOfBoundsException
	 */
	public function getProperty(PropertyId $propertyId) {
		return $this->getEntityDocument($propertyId);
	}

	/**
	 * @param EntityId $entityId
	 * @return EntityDocument
	 */
	public function getEntityDocument(EntityId $entityId) {
		try {
			return $this->cache->fetch($entityId);
		} catch(OutOfBoundsException $e) {
			$entity = $this->getEntityFromApi($entityId);
			$this->cache->save($entity);
			return $entity;
		}
	}

	private function getEntityFromApi(EntityId $entityId) {
		$entities = $this->getEntitiesFromApi(array($entityId));

		if(empty($entities)) {
			throw new OutOfBoundsException('The entity ' . $entityId->getSerialization(). ' does not exists');
		}

		return reset($entities);
	}

	private function getEntitiesFromApi(array $entityIds) {
		$revisions = $this->revisionsGetter->getRevisions($entityIds);

		$entities = array();

		foreach($revisions->toArray() as $revision) {
			$entities[] = $revision->getContent()->getNativeData();
		}

		return $entities;
	}
}
