<?php

namespace PPP\Wikidata\Wikipedia;

use Wikibase\DataModel\SiteLink;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class MediawikiArticleImageProvider extends PerSiteLinkProvider {

	/**
	 * @param SiteLink $siteLink
	 * @return MediawikiArticleImage
	 */
	public function getImageForSiteLink(SiteLink $siteLink) {
		return $this->getForSiteLink($siteLink);
	}

	protected function buildRequest($titles) {
		return array(
			'action' => 'query',
			'titles' => implode('|', $titles),
			'redirects' => true,
			'prop' => 'pageimages',
			'piprop' => 'thumbnail|name',
			'pithumbsize' => 300,
			'pilimit' => 50
		);
	}

	protected function parseResult($wikiId, $titles, $result) {
		$articleImages = array();

		foreach($result['query']['pages'] as $pageResult) {
			if(array_key_exists('thumbnail', $pageResult)) {
				$articleImages[] = new MediawikiArticleImage(
					new SiteLink($wikiId, $pageResult['title']),
					$pageResult['thumbnail']['source'],
					$pageResult['thumbnail']['width'],
					$pageResult['thumbnail']['height'],
					str_replace('_', ' ', $pageResult['pageimage'])
				);
			}
		}

		return $articleImages;
	}
}
