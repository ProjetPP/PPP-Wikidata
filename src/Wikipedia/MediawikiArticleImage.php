<?php

namespace PPP\Wikidata\Wikipedia;

class MediawikiArticleImage {

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
	 * @param string $url
	 * @param int $width
	 * @param int $height
	 * @param string $title
	 */
	public function __construct($url, $width, $height, $title) {
		$this->url = $url;
		$this->width = $width;
		$this->height = $height;
		$this->title = $title;
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
