<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\StringValue;
use Doctrine\Common\Cache\ArrayCache;
use PPP\Wikidata\Cache\JsonLdDataValueFormatterCache;

/**
 * @covers PPP\Wikidata\ValueFormatters\CachedJsonLdDataValueFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class CachedJsonLdDataValueFormatterTest extends \PHPUnit_Framework_TestCase {

	public function testWithCacheHit() {
		$formatterMock = $this->getMock('PPP\Wikidata\ValueFormatters\JsonLd\JsonLdDataValueFormatter');
		$formatterMock->expects($this->never())
			->method('format');

		$cache = new JsonLdDataValueFormatterCache(new ArrayCache(), 'foo');
		$cache->save(new StringValue('foo'), (object) array('bar' => 1));

		$formatter = new CachedJsonLdDataValueFormatter($formatterMock, $cache);

		$this->assertEquals((object) array('bar' => 1), $formatter->format(new StringValue('foo')));
	}

	public function testWithCacheMiss() {
		$formatterMock = $this->getMock('PPP\Wikidata\ValueFormatters\JsonLd\JsonLdDataValueFormatter');
		$formatterMock->expects($this->once())
			->method('format')
			->with($this->equalTo(new StringValue('foo')))
			->will($this->returnValue((object) array('bar' => 1)));

		$cache = new JsonLdDataValueFormatterCache(new ArrayCache(), 'foo');

		$formatter = new CachedJsonLdDataValueFormatter($formatterMock, $cache);

		$this->assertEquals((object) array('bar' => 1), $formatter->format(new StringValue('foo')));
		$this->assertTrue($cache->contains(new StringValue('foo')));
	}
}
