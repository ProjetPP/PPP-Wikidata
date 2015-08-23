<?php

namespace PPP\Wikidata\Cache;

use DataValues\DataValue;
use Doctrine\Common\Cache\Cache;
use OutOfBoundsException;
use RuntimeException;
use stdClass;

/**
 * Cache whose keys are based on SiteLink
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdDataValueFormatterCache {

	const CACHE_ID_PREFIX = 'ppp-wd-jsonld-dv-';

	const CACHE_LIFE_TIME = 86400;

	/**
	 * @var Cache
	 */
	private $cache;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @param Cache $cache
	 */
	public function __construct(Cache $cache, $name) {
		$this->cache = $cache;
		$this->name = $name;
	}

	/**
	 * @param DataValue $dataValue
	 * @return stdClass
	 */
	public function fetch(DataValue $dataValue) {
		$result = $this->cache->fetch($this->getCacheId($dataValue));

		if($result === false) {
			throw new OutOfBoundsException('The search is not in the cache.');
		}

		return $result;
	}

	/**
	 * @param DataValue $dataValue
	 * @return bool
	 */
	public function contains(DataValue $dataValue) {
		return $this->cache->contains($this->getCacheId($dataValue));
	}

	/**
	 * @param DataValue $dataValue
	 * @param stdClass $jsonLd
	 */
	public function save(DataValue $dataValue, stdClass $jsonLd) {
		if(!$this->cache->save(
			$this->getCacheId($dataValue),
			$jsonLd,
			self::CACHE_LIFE_TIME
		)) {
			throw new RuntimeException('The cache failed to save.');
		}
	}

	private function getCacheId(DataValue $dataValue) {
		return self::CACHE_ID_PREFIX . '-' . $this->name . '-' . $dataValue->getHash();
	}
}
