<?php

namespace PPP\Wikidata\Wikipedia;

use Wikibase\DataModel\SiteLink;

class MediawikiArticleImage implements SiteLinkProvider {

	/**
	 * @var SiteLink
	 */
	private $siteLink;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var int
	 */
	private $width;

	/**
	 * @var int
	 */
	private $height;

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @param SiteLink $siteLink
	 * @param string $url
	 * @param int $width
	 * @param int $height
	 * @param string $title
	 */
	public function __construct(SiteLink $siteLink, $url, $width, $height, $title) {
		$this->siteLink = $siteLink;
		$this->url = $url;
		$this->width = $width;
		$this->height = $height;
		$this->title = $title;
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
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @return int
	 */
	public function getWidth() {
		return $this->width;
	}

	/**
	 * @return int
	 */
	public function getHeight() {
		return $this->height;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
}
