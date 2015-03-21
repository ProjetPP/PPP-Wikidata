<?php

namespace PPP\Wikidata\Wikipedia;

use Wikibase\DataModel\SiteLink;

/**
 * @covers PPP\Wikidata\Wikipedia\MediawikiArticle
 *
 * @licence MIT
 * @author Thomas Pellissier Tanon
 */
class MediawikiArticleTest extends \PHPUnit_Framework_TestCase {

	public function testGetSiteLink() {
		$articleHeader = new MediawikiArticle(new SiteLink('enwiki', 'bar'), 'foo', 'en', 'http://test.org');
		$this->assertEquals(new SiteLink('enwiki', 'bar'), $articleHeader->getSiteLink());
	}

	public function testGetHeaderText() {
		$articleHeader = new MediawikiArticle(new SiteLink('enwiki', 'bar'), 'foo', 'en', 'http://test.org');
		$this->assertEquals('foo', $articleHeader->getHeaderText());
	}

	public function testGetLanguageCode() {
		$articleHeader = new MediawikiArticle(new SiteLink('enwiki', 'bar'), 'foo', 'en', 'http://test.org');
		$this->assertEquals('en', $articleHeader->getLanguageCode());
	}

	public function testGetUrl() {
		$articleHeader = new MediawikiArticle(new SiteLink('enwiki', 'bar'), 'foo', 'en', 'http://test.org');
		$this->assertEquals('http://test.org', $articleHeader->getUrl());
	}

	public function testGetImage() {
		$articleHeader = new MediawikiArticle(
			new SiteLink('enwiki', 'bar'),
			'foo',
			'en',
			'http://test.org',
			new MediawikiArticleImage('http://test.org', 1, 1, 'foo')
		);
		$this->assertEquals('foo', $articleHeader->getHeaderText());
	}
}
