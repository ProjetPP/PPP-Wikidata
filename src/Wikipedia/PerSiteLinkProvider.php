<?php

namespace PPP\Wikidata\Wikipedia;

use InvalidArgumentException;
use Mediawiki\Api\MediawikiApi;
use OutOfBoundsException;
use PPP\Wikidata\Cache\PerSiteLinkCache;
use Wikibase\DataModel\SiteLink;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
abstract class PerSiteLinkProvider {

	/**
	 * @var PerSiteLinkCache
	 */
	private $cache;

	/**
	 * @var MediawikiApi[]
	 */
	private $apis;

	/**
	 * @param MediawikiApi[] $apis
	 * @param PerSiteLinkCache $cache
	 */
	public function __construct(array $apis, PerSiteLinkCache $cache) {
		$this->apis = $apis;
		$this->cache = $cache;
	}

	/**
	 * @param SiteLink $siteLink
	 * @return SiteLinkProvider
	 */
	protected function getForSiteLink(SiteLink $siteLink) {
		try {
			return $this->cache->fetch($siteLink);
		} catch(OutOfBoundsException $e) {
			$headers = $this->getFromWiki($siteLink->getSiteId(), array($siteLink->getPageName()));
			if(empty($headers)) {
				throw new OutOfBoundsException('The page ' . $siteLink->getPageName() . ' of ' . $siteLink->getSiteId() . ' does not exists');
			}

			return reset($headers);
		}
	}

	/**
	 * Makes sure that data are loaded into the cache
	 *
	 * @param SiteLink[] $titles
	 */
	public function loadFromSiteLinks(array $titles) {
		$titlesPerWiki = array();

		foreach($titles as $title) {
			if(!$this->cache->contains($title)) {
				$titlesPerWiki[$title->getSiteId()][] = $title->getPageName();
			}
		}

		foreach($titlesPerWiki as $wikiId => $titles) {
			foreach($this->getFromWiki($wikiId, $titles) as $articleHeader) {
				$this->cache->save($articleHeader);
			}
		}
	}

	private function getFromWiki($wikiId, $titles) {
		if(empty($titles)) {
			return array();
		}

		$request = $this->buildRequest($titles);
		$api = $this->getApiFromWikiId($wikiId);
		$finalResults = array();
		$result = array('continue' => '');
		do {
			$request['continue'] = $result['continue'];
			$result =  $api->getAction($request['action'], $request);
			$finalResults = array_merge($finalResults, $this->parseResult($wikiId, $titles, $result));

		} while(array_key_exists('continue', $result));

		return $finalResults;
	}

	/**
	 * @param string[] $titles
	 * @return array
	 */
	protected abstract function buildRequest($titles);

	/**
	 * @param string $wikiId
	 * @param string[] $titles
	 * @param array $result
	 * @return SiteLinkProvider[]
	 */
	protected abstract function parseResult($wikiId, $titles, $result);

	/**
	 * @param string $wikiId
	 * @return MediawikiApi
	 */
	protected function getApiFromWikiId($wikiId) {
		if(!array_key_exists($wikiId, $this->apis)) {
			throw new InvalidArgumentException('Unknown wiki id: ' . $wikiId);
		}

		return $this->apis[$wikiId];
	}

	/**
	 * @param string $wikiId
	 * @return bool
	 */
	public function isWikiIdSupported($wikiId) {
		return array_key_exists($wikiId, $this->apis);
	}
}
