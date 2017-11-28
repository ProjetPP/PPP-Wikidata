<?php

namespace PPP\Wikidata\Wikipedia;

use Wikibase\DataModel\SiteLink;

/**
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class MediawikiArticleProvider extends PerSiteLinkProvider {

	/**
	 * @param SiteLink $siteLink
	 * @return MediawikiArticle
	 */
	public function getArticleForSiteLink(SiteLink $siteLink) {
		return $this->getForSiteLink($siteLink);
	}

	protected function buildRequest($titles) {
		return array(
			'action' => 'query',
			'titles' => implode('|', $titles),
			'prop' => 'extracts|info|pageimages',
			'inprop' => 'url',
			'redirects' => true,
			'exintro' => true,
			'exsectionformat' => 'plain',
			'explaintext' => true,
			'exsentences' => 3,
			'exlimit' => 20,
			'piprop' => 'thumbnail|name',
			'pithumbsize' => 300,
			'pilimit' => 20
		);
	}

	protected function parseResult($wikiId, $titles, $result) {
		$articleHeaders = array();

		foreach($result['query']['pages'] as $pageResult) {
			if(array_key_exists('extract', $pageResult)) {
				$image = null;

				if(array_key_exists('thumbnail', $pageResult)) {
					$image = new MediawikiArticleImage(
						$pageResult['thumbnail']['source'],
						$pageResult['thumbnail']['width'],
						$pageResult['thumbnail']['height'],
						str_replace('_', ' ', $pageResult['pageimage'])
					);
				}

				$articleHeaders[] = new MediawikiArticle(
					new SiteLink($wikiId, $pageResult['title']),
					$pageResult['extract'],
					$pageResult['pagelanguage'],
					$pageResult['canonicalurl'],
					$image
				);

			}
		}

		return $articleHeaders;
	}
}
