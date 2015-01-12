<?php

namespace PPP\Wikidata\Cache;

use Doctrine\Common\Cache\Cache;
use OutOfBoundsException;
use PPP\Wikidata\Wikipedia\SiteLinkProvider;
use RuntimeException;
use Wikibase\DataModel\SiteLink;

/**
 * Cache whose keys are based on SiteLink
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class PerSiteLinkCache {

	const CACHE_ID_PREFIX = 'ppp-wd-pslc-';

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
	 * @param string $name
	 */
	public function __construct(Cache $cache, $name) {
		$this->cache = $cache;
		$this->name = $name;
	}

	/**
	 * @param SiteLink $siteLink
	 * @return SiteLinkProvider
	 */
	public function fetch(SiteLink $siteLink) {
		$result = $this->cache->fetch($this->getCacheId($siteLink));

		if($result === false) {
			throw new OutOfBoundsException('The search is not in the cache.');
		}

		return $result;
	}

	/**
	 * @param SiteLink $siteLink
	 * @return bool
	 */
	public function contains(SiteLink $siteLink) {
		return $this->cache->contains($this->getCacheId($siteLink));
	}

	/**
	 * @param SiteLinkProvider $value
	 */
	public function save(SiteLinkProvider $value) {
		if(!$this->cache->save(
			$this->getCacheId($value->getSiteLink()),
			$value,
			self::CACHE_LIFE_TIME
		)) {
			throw new RuntimeException('The cache failed to save.');
		}
	}

	private function getCacheId(SiteLink $siteLink) {
		return self::CACHE_ID_PREFIX . '-' . $this->name . '-' . $siteLink->getSiteId() . '-' . md5($this->normalizeTitle($siteLink->getPageName()));
	}

	private function normalizeTitle($title) {
		return str_replace('_', ' ', $title);
	}
}
