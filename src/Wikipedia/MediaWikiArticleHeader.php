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

	public function getText() {
		return $this->text;
	}

	public function getLanguageCode() {
		return $this->languageCode;
	}

	public function getUrl() {
		return $this->url;
	}
}
