<?php

namespace PPP\Wikidata\Wikipedia;

use Wikibase\DataModel\SiteLink;

class MediawikiArticle implements SiteLinkProvider {

	/**
	 * @var SiteLink
	 */
	private $siteLink;

	/**
	 * @var string
	 */
	private $headerText;

	/**
	 * @var string
	 */
	private $languageCode;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var MediawikiArticleImage|null
	 */
	private $image;

	/**
	 * @param SiteLink $siteLink
	 * @param string $headerText
	 * @param string $languageCode
	 * @param string $url
	 * @param MediawikiArticleImage $image
	 */
	public function __construct(SiteLink $siteLink, $headerText, $languageCode, $url, MediawikiArticleImage $image = null) {
		$this->siteLink = $siteLink;
		$this->headerText = $headerText;
		$this->languageCode = $languageCode;
		$this->url = $url;
		$this->image = $image;
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
	public function getHeaderText() {
		return $this->headerText;
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

	/**
	 * @return MediawikiArticleImage|null
	 */
	public function getImage() {
		return $this->image;
	}
}
