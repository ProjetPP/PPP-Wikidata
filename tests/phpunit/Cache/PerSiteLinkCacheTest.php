<?php

namespace PPP\Wikidata\Cache;

use Doctrine\Common\Cache\ArrayCache;
use PPP\Wikidata\Wikipedia\MediawikiArticle;
use Wikibase\DataModel\SiteLink;

/**
 * @covers PPP\Wikidata\Cache\PerSiteLinkCache
 *
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class PerSiteLinkCacheTest extends \PHPUnit_Framework_TestCase {

	public function testFetch() {
		$cache = new PerSiteLinkCache(new ArrayCache(), 'test');
		$cache->save(new MediawikiArticle(new SiteLink('enwiki', 'bar'), 'foo', 'en', 'http://test.org'));

		$this->assertEquals(
			new MediawikiArticle(new SiteLink('enwiki', 'bar'), 'foo', 'en', 'http://test.org'),
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
		$cache->save(new MediawikiArticle(new SiteLink('enwiki', 'b ar'), 'foo', 'en', 'http://test.org'));

		$this->assertTrue($cache->contains(new SiteLink('enwiki', 'b_ar')));
	}

	public function testContainsFalse() {
		$cacheBackend = new ArrayCache();
		$cache = new PerSiteLinkCache($cacheBackend, 'test');

		$cache2 = new PerSiteLinkCache($cacheBackend, 'test2');
		$cache2->save(new MediawikiArticle(new SiteLink('enwiki', 'bar'), 'foo', 'en', 'http://test.org'));

		$this->assertFalse($cache->contains(new SiteLink('enwiki', 'bar')));
	}
}
