<?php

namespace PPP\Wikidata\ValueParsers;

use DataValues\GlobeCoordinateValue;
use DataValues\LatLongValue;
use DataValues\MonolingualTextValue;
use DataValues\StringValue;
use Mediawiki\Api\MediawikiApi;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\EntityStore\Api\ApiEntityStore;

/**
 * @covers PPP\Wikidata\ValueParsers\WikibaseValueParserFactory
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 *
 * @todo mock instead of requests to the real API?
 */
class WikibaseValueParserFactoryTest extends \PHPUnit_Framework_TestCase {

	private function newFactory() {
		return new WikibaseValueParserFactory(
			'fr',
			new ApiEntityStore(new MediawikiApi('http://www.wikidata.org/w/api.php'))
		);
	}

	public function testParserParseCommonsMedia() {
		$this->assertEquals(
			array(new StringValue('Foo.jpg')),
			$this->newFactory()->newWikibaseValueParser()->parse('Foo.jpg', 'commonsMedia')
		);
	}

	public function testParserParseGlobeCoordinate() {
		$this->assertEquals(
			array(new GlobeCoordinateValue(new LatLongValue(42, 42), 1)),
			$this->newFactory()->newWikibaseValueParser()->parse('42°N, 42°E', 'globe-coordinate')
		);
	}

	public function testParserParseMonolingualText() {
		$this->assertEquals(
			array(new MonolingualTextValue('en', 'Foo')),
			$this->newFactory()->newWikibaseValueParser()->parse('Foo', 'monolingualtext')
		);
	}

	public function testParserParseString() {
		$this->assertEquals(
			array(new StringValue('foo')),
			$this->newFactory()->newWikibaseValueParser()->parse('foo', 'string')
		);
	}

	public function testParserParseUrl() {
		$this->assertEquals(
			array(new StringValue('http://exemple.org')),
			$this->newFactory()->newWikibaseValueParser()->parse('http://exemple.org', 'url')
		);
	}

	public function testParserParseWikibaseItem() {
		$this->assertEquals(
			array(new EntityIdValue(new ItemId('Q76'))),
			$this->newFactory()->newWikibaseValueParser()->parse('Barack Obama', 'wikibase-item')
		);
	}

	public function testParserParseWikibaseProperty() {
		$this->assertEquals(
			array(new EntityIdValue(new PropertyId('P569'))),
			$this->newFactory()->newWikibaseValueParser()->parse('Date de naissance', 'wikibase-property')
		);
	}
}
