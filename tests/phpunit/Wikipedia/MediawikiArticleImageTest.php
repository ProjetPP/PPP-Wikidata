<?php

namespace PPP\Wikidata;

use PPP\Wikidata\Wikipedia\MediawikiArticleImage;

/**
 * @covers PPP\Wikidata\Wikipedia\MediawikiArticleImage
 *
 * @licence MIT
 * @author Thomas Pellissier Tanon
 */
class MediawikiArticleImageTest extends \PHPUnit_Framework_TestCase {

	public function testGetUrl() {
		$articleImage = new MediawikiArticleImage('http://test.org', 1, 1, 'foo');
		$this->assertEquals('http://test.org', $articleImage->getUrl());
	}

	public function testGetWidth() {
		$articleImage = new MediawikiArticleImage('http://test.org', 1, 1, 'foo');
		$this->assertEquals(1, $articleImage->getWidth());
	}

	public function testGetHeight() {
		$articleImage = new MediawikiArticleImage('http://test.org', 1, 1, 'foo');
		$this->assertEquals(1, $articleImage->getHeight());
	}

	public function testGetTitle() {
		$articleImage = new MediawikiArticleImage('http://test.org', 1, 1, 'foo');
		$this->assertEquals('foo', $articleImage->getTitle());
	}
}
