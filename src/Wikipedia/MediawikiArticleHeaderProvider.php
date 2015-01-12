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
class MediawikiArticleHeaderProvider {

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
	 * @return MediawikiArticleHeader
	 */
	public function getHeaderForSiteLink(SiteLink $siteLink) {
		try {
			return $this->cache->fetch($siteLink);
		} catch(OutOfBoundsException $e) {
			$headers = $this->getHeadersFromWiki($siteLink->getSiteId(), array($siteLink->getPageName()));
			if(empty($headers)) {
				throw new OutOfBoundsException('The page ' . $siteLink->getPageName() . ' of ' . $siteLink->getSiteId() . ' does not exists');
			}

			return reset($headers);
		}
	}

	/**
	 * Makes sure that article headers are loaded into the cache
	 *
	 * @param SiteLink[] $titles
	 */
	public function loadHeaders(array $titles) {
		$titlesPerWiki = array();

		foreach($titles as $title) {
			if(!$this->cache->contains($title)) {
				$titlesPerWiki[$title->getSiteId()][] = $title->getPageName();
			}
		}

		foreach($titlesPerWiki as $wikiId => $titles) {
			foreach($this->getHeadersFromWiki($wikiId, $titles) as $articleHeader) {
				$this->cache->save($articleHeader);
			}
		}
	}

	private function getHeadersFromWiki($wikiId, $titles) {
		if(empty($titles)) {
			return array();
		}

		$result = $this->getApiFromWiki($wikiId)->getAction('query', array(
			'titles' => implode('|', $titles),
			'prop' => 'extracts|info',
			'inprop' => 'url',
			'redirects' => true,
			'exintro' => true,
			'exsectionformat' => 'plain',
			'explaintext' => true,
			'exsentences' => 3,
			'exlimit' => count($titles)
		));

		$articleHeaders = array();
		foreach($result['query']['pages'] as $pageResult) {
			if(array_key_exists('extract', $pageResult)) {
				$articleHeaders[] = new MediawikiArticleHeader(
					new SiteLink($wikiId, $pageResult['title']),
					$pageResult['extract'],
					$pageResult['pagelanguage'],
					$pageResult['canonicalurl']
				);
			}
		}

		return $articleHeaders;
	}

	private function getApiFromWiki($wiki) {
		if(!array_key_exists($wiki, $this->apis)) {
			throw new InvalidArgumentException('Unknown wiki id: ' . $wiki);
		}

		return $this->apis[$wiki];
	}

	/**
	 * @param string $wikiId
	 * @return bool
	 */
	public function isWikiIdSupported($wikiId) {
		return array_key_exists($wikiId, $this->apis);
	}
}
