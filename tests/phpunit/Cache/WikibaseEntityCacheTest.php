<?php

namespace PPP\Wikidata\Cache;

use Doctrine\Common\Cache\ArrayCache;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers PPP\Wikidata\Cache\WikibaseEntityCache
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseEntityCacheTest extends \PHPUnit_Framework_TestCase {

	public function testFetch() {
		$cache = new WikibaseEntityCache(new ArrayCache());

		$item = Item::newEmpty();
		$item->setId(new ItemId('Q42'));
		$cache->save($item);

		$this->assertEquals($item, $cache->fetch(new ItemId('Q42')));
	}

	public function testFetchWithException() {
		$this->setExpectedException('\OutOfBoundsException');

		$cache = new WikibaseEntityCache(new ArrayCache());
		$cache->fetch(new ItemId('Q42'));
	}

	public function testContainsTrue() {
		$cache = new WikibaseEntityCache(new ArrayCache());

		$item = Item::newEmpty();
		$item->setId(new ItemId('Q42'));
		$cache->save($item);

		$this->assertTrue($cache->contains(new ItemId('Q42')));
	}

	public function testContainsFalse() {
		$cache = new WikibaseEntityCache(new ArrayCache());

		$this->assertFalse($cache->contains(new ItemId('Q42')));
	}
}
