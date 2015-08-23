<?php

namespace PPP\Wikidata\Cache;

use DataValues\StringValue;
use Doctrine\Common\Cache\ArrayCache;

/**
 * @covers PPP\Wikidata\Cache\JsonLdDataValueFormatterCache
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdDataValueFormatterCacheTest extends \PHPUnit_Framework_TestCase {

	public function testFetch() {
		$value = (object) array('a' => 1);

		$cache = new JsonLdDataValueFormatterCache(new ArrayCache(), 'test');
		$cache->save(new StringValue('foo'), $value);

		$this->assertEquals(
			$value,
			$cache->fetch(new StringValue('foo'))
		);
	}

	public function testFetchWithException() {
		$this->setExpectedException('\OutOfBoundsException');

		$cache = new JsonLdDataValueFormatterCache(new ArrayCache(), 'test');
		$cache->fetch(new StringValue('foo'));
	}

	public function testContainsTrue() {
		$cache = new JsonLdDataValueFormatterCache(new ArrayCache(), 'test');
		$cache->save(new StringValue('foo'), (object) array('a' => 1));

		$this->assertTrue($cache->contains(new StringValue('foo')));
	}

	public function testContainsFalse() {
		$cacheBackend = new ArrayCache();
		$cache = new JsonLdDataValueFormatterCache($cacheBackend, 'test');

		$cache2 = new JsonLdDataValueFormatterCache($cacheBackend, 'test2');
		$cache2->save(new StringValue('foo'), (object) array('a' => 1));

		$this->assertFalse($cache->contains(new StringValue('foo')));
	}
}
