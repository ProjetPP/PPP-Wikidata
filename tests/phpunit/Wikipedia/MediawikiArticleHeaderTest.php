<?php

namespace PPP\Wikidata;

use PPP\Wikidata\Wikipedia\MediawikiArticleHeader;
use Wikibase\DataModel\SiteLink;

/**
 * @covers PPP\Wikidata\Wikipedia\MediawikiArticleHeader
 *
 * @licence MIT
 * @author Thomas Pellissier Tanon
 */
class MediawikiArticleHeaderTest extends \PHPUnit_Framework_TestCase {

	public function testGetSiteLink() {
		$articleHeader = new MediawikiArticleHeader(new SiteLink('enwiki', 'bar'), 'foo', 'en', 'http://test.org');
		$this->assertEquals(new SiteLink('enwiki', 'bar'), $articleHeader->getSiteLink());
	}

	public function testGetText() {
		$articleHeader = new MediawikiArticleHeader(new SiteLink('enwiki', 'bar'), 'foo', 'en', 'http://test.org');
		$this->assertEquals('foo', $articleHeader->getText());
	}

	public function testGetLanguageCode() {
		$articleHeader = new MediawikiArticleHeader(new SiteLink('enwiki', 'bar'), 'foo', 'en', 'http://test.org');
		$this->assertEquals('en', $articleHeader->getLanguageCode());
	}

	public function testGetUrl() {
		$articleHeader = new MediawikiArticleHeader(new SiteLink('enwiki', 'bar'), 'foo', 'en', 'http://test.org');
		$this->assertEquals('http://test.org', $articleHeader->getUrl());
	}
}
