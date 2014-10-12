<?php

namespace PPP\Wikidata\ValueParsers;

use DataValues\GlobeCoordinateValue;
use DataValues\LatLongValue;
use DataValues\StringValue;
use Mediawiki\Api\MediawikiApi;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers PPP\Wikidata\ValueParsers\WikibaseValueParserFactory
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseValueParserFactoryTest extends \PHPUnit_Framework_TestCase {

	private function newFactory() {
		return new WikibaseValueParserFactory('fr', new MediawikiApi('http://www.wikidata.org/w/api.php'));
	}

	public function testParserParseString() {
		$this->assertEquals(
			new StringValue('foo'),
			$this->newFactory()->newWikibaseValueParser()->parse('foo', 'string')
		);
	}

	public function testParserParseWikibaseItem() {
		$this->assertEquals(
			new EntityIdValue(new ItemId('Q42')),
			$this->newFactory()->newWikibaseValueParser()->parse('Douglas Adams', 'wikibase-item')
		);
	}

	public function testParserParseWikibaseProperty() {
		$this->assertEquals(
			new EntityIdValue(new PropertyId('P569')),
			$this->newFactory()->newWikibaseValueParser()->parse('Date de naissance', 'wikibase-property')
		);
	}

	public function testParserParseGlobeCoordinate() {
		$this->assertEquals(
			new GlobeCoordinateValue(new LatLongValue(42, 42), 1),
			$this->newFactory()->newWikibaseValueParser()->parse('42°N, 42°E', 'globecoordinate')
		);
	}
}
