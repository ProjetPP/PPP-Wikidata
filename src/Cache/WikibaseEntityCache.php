<?php

namespace PPP\Wikidata\Cache;

use Doctrine\Common\Cache\Cache;
use OutOfBoundsException;
use RuntimeException;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\EntityId;

/**
 * Cache of Entity objects.
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 *
 * @todo Serialize entity before caching them?
 */
class WikibaseEntityCache {

	const CACHE_ID_PREFIX = 'ppp-wd-entity-';

	const CACHE_LIFE_TIME = 86400;

	/**
	 * @var Cache
	 */
	private $cache;

	/**
	 * @param Cache $cache
	 */
	public function __construct(Cache $cache) {
		$this->cache = $cache;
	}

	/**
	 * Returns an Entity from the cache
	 *
	 * @param EntityId $entityId
	 * @return EntityDocument
	 * @throws OutOfBoundsException
	 */
	public function fetch(EntityId $entityId) {
		$result = $this->cache->fetch($this->getCacheIdFromEntityId($entityId));

		if($result === false) {
			throw new OutOfBoundsException('The entity ' . $entityId->getSerialization() . ' is not in the cache.');
		}

		return $result;
	}

	/**
	 * Tests if an Entity exists in the cache.
	 *
	 * @param EntityId $entityId
	 * @return bool
	 */
	public function contains($entityId) {
		return $this->cache->contains($this->getCacheIdFromEntityId($entityId));
	}

	/**
	 * Save an Entity in the cache.
	 *
	 * @param EntityDocument $entity
	 */
	public function save(EntityDocument $entity) {
		if(!$this->cache->save(
			$this->getCacheIdFromEntityId($entity->getId()),
			$entity,
			self::CACHE_LIFE_TIME
		)) {
			throw new RuntimeException('The cache failed to save ' . $entity->getId()->getSerialization());
		}
	}

	private function getCacheIdFromEntityId(EntityId $entityId) {
		return self::CACHE_ID_PREFIX . WIKIBASE_DATAMODEL_VERSION . '-' . $entityId->getSerialization();
	}
}
