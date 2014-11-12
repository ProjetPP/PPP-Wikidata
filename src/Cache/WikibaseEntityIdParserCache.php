<?php

namespace PPP\Wikidata\Cache;

use Doctrine\Common\Cache\Cache;
use OutOfBoundsException;
use RuntimeException;
use Wikibase\DataModel\Entity\EntityId;

/**
 * Cache of Entity objects.
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseEntityIdParserCache {

	const CACHE_ID_PREFIX = 'ppp-wd-eip-';

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
	 * @param string $search
	 * @param string$entityType
	 * @param string $languageCode
	 * @return EntityId[]
	 */
	public function fetch($search, $entityType, $languageCode) {
		$result = $this->cache->fetch($this->getCacheId($search, $entityType, $languageCode));

		if($result === false) {
			throw new OutOfBoundsException('The search is not in the cache.');
		}

		return $result;
	}

	/**
	 * @param string $search
	 * @param string$entityType
	 * @param string $languageCode
	 * @return boolean
	 */
	public function contains($search, $entityType, $languageCode) {
		return $this->cache->contains($this->getCacheId($search, $entityType, $languageCode));
	}

	/**
	 * @param string $search
	 * @param string $entityType
	 * @param string $languageCode
	 * @param EntityId[] $result
	 */
	public function save($search, $entityType, $languageCode, array $result) {
		if(!$this->cache->save(
			$this->getCacheId($search, $entityType, $languageCode),
			$result,
			self::CACHE_LIFE_TIME
		)) {
			throw new RuntimeException('The cache failed to save.');
		}
	}

	private function getCacheId($search, $entityType, $languageCode) {
		return self::CACHE_ID_PREFIX . WIKIBASE_DATAMODEL_VERSION . '-' . md5($entityType . '-' . $languageCode . '-' . $search);
	}
}

