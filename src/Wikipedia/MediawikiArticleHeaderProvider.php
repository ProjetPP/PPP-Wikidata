<?php

namespace PPP\Wikidata\Wikipedia;

use Wikibase\DataModel\SiteLink;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class MediawikiArticleHeaderProvider extends PerSiteLinkProvider {

	/**
	 * @param SiteLink $siteLink
	 * @return MediawikiArticleHeader
	 */
	public function getHeaderForSiteLink(SiteLink $siteLink) {
		return $this->getForSiteLink($siteLink);
	}

	protected function buildRequest($titles) {
		return array(
			'action' => 'query',
			'titles' => implode('|', $titles),
			'prop' => 'extracts|info',
			'inprop' => 'url',
			'redirects' => true,
			'exintro' => true,
			'exsectionformat' => 'plain',
			'explaintext' => true,
			'exsentences' => 3,
			'exlimit' => 20
		);
	}

	protected function parseResult($wikiId, $titles, $result) {
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
}
