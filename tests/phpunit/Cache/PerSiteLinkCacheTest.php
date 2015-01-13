<?php

namespace PPP\Wikidata\Cache;

use Doctrine\Common\Cache\ArrayCache;
use PPP\Wikidata\Wikipedia\MediawikiArticleHeader;
use Wikibase\DataModel\SiteLink;

/**
 * @covers PPP\Wikidata\Cache\PerSiteLinkCache
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class PerSiteLinkCacheTest extends \PHPUnit_Framework_TestCase {

	public function testFetch() {
		$cache = new PerSiteLinkCache(new ArrayCache(), 'test');
		$cache->save(new MediawikiArticleHeader(new SiteLink('enwiki', 'bar'), 'foo', 'en', 'http://test.org'));

		$this->assertEquals(
			new MediawikiArticleHeader(new SiteLink('enwiki', 'bar'), 'foo', 'en', 'http://test.org'),
			$cache->fetch(new SiteLink('enwiki', 'bar'))
		);
	}

	public function testFetchWithException() {
		$this->setExpectedException('\OutOfBoundsException');

		$cache = new PerSiteLinkCache(new ArrayCache(), 'test');
		$cache->fetch(new SiteLink('foo', 'bar'));
	}

	public function testContainsTrue() {
		$cache = new PerSiteLinkCache(new ArrayCache(), 'test');
		$cache->save(new MediawikiArticleHeader(new SiteLink('enwiki', 'b ar'), 'foo', 'en', 'http://test.org'));

		$this->assertTrue($cache->contains(new SiteLink('enwiki', 'b_ar')));
	}

	public function testContainsFalse() {
		$cache = new PerSiteLinkCache(new ArrayCache(), 'test');

		$cache2 = new PerSiteLinkCache(new ArrayCache(), 'test2');
		$cache2->save(new MediawikiArticleHeader(new SiteLink('enwiki', 'bar'), 'foo', 'en', 'http://test.org'));

		$this->assertFalse($cache->contains(new SiteLink('enwiki', 'bar')));
	}
}
