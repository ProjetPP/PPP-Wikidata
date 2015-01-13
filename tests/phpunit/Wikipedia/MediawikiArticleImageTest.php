<?php

namespace PPP\Wikidata;

use PPP\Wikidata\Wikipedia\MediawikiArticleImage;
use Wikibase\DataModel\SiteLink;

/**
 * @covers PPP\Wikidata\MediawikiArticleImage
 *
 * @licence MIT
 * @author Thomas Pellissier Tanon
 */
class MediawikiArticleImageTest extends \PHPUnit_Framework_TestCase {

	public function testGetSiteLink() {
		$articleImage = new MediawikiArticleImage(new SiteLink('enwiki', 'bar'), 'http://test.org', 1, 1, 'foo');
		$this->assertEquals(new SiteLink('enwiki', 'bar'), $articleImage->getSiteLink());
	}

	public function testGetUrl() {
		$articleImage = new MediawikiArticleImage(new SiteLink('enwiki', 'bar'), 'http://test.org', 1, 1, 'foo');
		$this->assertEquals('http://test.org', $articleImage->getUrl());
	}

	public function testGetWidth() {
		$articleImage = new MediawikiArticleImage(new SiteLink('enwiki', 'bar'), 'http://test.org', 1, 1, 'foo');
		$this->assertEquals(1, $articleImage->getWidth());
	}

	public function testGetHeight() {
		$articleImage = new MediawikiArticleImage(new SiteLink('enwiki', 'bar'), 'http://test.org', 1, 1, 'foo');
		$this->assertEquals(1, $articleImage->getHeight());
	}

	public function testGetTitle() {
		$articleImage = new MediawikiArticleImage(new SiteLink('enwiki', 'bar'), 'http://test.org', 1, 1, 'foo');
		$this->assertEquals('foo', $articleImage->getTitle());
	}
}
