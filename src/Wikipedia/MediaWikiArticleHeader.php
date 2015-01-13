<?php

namespace PPP\Wikidata\Wikipedia;

use Wikibase\DataModel\SiteLink;

class MediawikiArticleHeader implements SiteLinkProvider {

	/**
	 * @var SiteLink
	 */
	private $siteLink;

	/**
	 * @var string
	 */
	private $text;

	/**
	 * @var string
	 */
	private $languageCode;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @param SiteLink $siteLink
	 * @param string $text
	 * @param string $languageCode
	 * @param string $url
	 */
	public function __construct(SiteLink $siteLink, $text, $languageCode, $url) {
		$this->siteLink = $siteLink;
		$this->text = $text;
		$this->languageCode = $languageCode;
		$this->url = $url;
	}

	/**
	 * @see SiteLinkProvider::getSiteLink
	 */
	public function getSiteLink() {
		return $this->siteLink;
	}

	/**
	 * @return string
	 */
	public function getText() {
		return $this->text;
	}

	/**
	 * @return string
	 */
	public function getLanguageCode() {
		return $this->languageCode;
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}
}
