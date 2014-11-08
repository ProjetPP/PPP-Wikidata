<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\DecimalValue;
use DataValues\GlobeCoordinateValue;
use DataValues\LatLongValue;
use DataValues\MonolingualTextValue;
use DataValues\QuantityValue;
use DataValues\StringValue;
use DataValues\TimeValue;
use Doctrine\Common\Cache\ArrayCache;
use Mediawiki\Api\MediawikiApi;
use PPP\DataModel\StringResourceNode;
use PPP\DataModel\TimeResourceNode;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers PPP\Wikidata\ValueFormatters\WikibaseValueFormatterFactory
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 *
 * @todo mock instead of requests to the real API?
 */
class WikibaseValueFormatterFactoryTest extends \PHPUnit_Framework_TestCase {

	private function newFactory() {
		return new WikibaseValueFormatterFactory('fr', new MediawikiApi('http://www.wikidata.org/w/api.php'), new ArrayCache());
	}

	public function testFormatterFormatGlobeCoordinate() {
		$this->assertEquals(
			new StringResourceNode('42, 42'),
			$this->newFactory()->newWikibaseValueFormatter()->format(
				new GlobeCoordinateValue(new LatLongValue(42, 42), 1)
			)
		);
	}

	public function testFormatterFormatMonolingualText() {
		$this->assertEquals(
			new StringResourceNode('foo', 'en'),
			$this->newFactory()->newWikibaseValueFormatter()->format(new MonolingualTextValue('en', 'foo'))
		);
	}

	public function testFormatterFormatQuantity() {
		$this->assertEquals(
			new StringResourceNode('491268±1'),
			$this->newFactory()->newWikibaseValueFormatter()->format(
				new QuantityValue(new DecimalValue('+491268'), '1', new DecimalValue('+491268'), new DecimalValue('+491267'))
			)
		);
	}

	public function testFormatterFormatString() {
		$this->assertEquals(
			new StringResourceNode('foo'),
			$this->newFactory()->newWikibaseValueFormatter()->format(new StringValue('foo'))
		);
	}

	public function testFormatterFormatTime() {
		$this->assertEquals(
			new TimeResourceNode('+00000001952-03-11T00:00:00Z'),
			$this->newFactory()->newWikibaseValueFormatter()->format(
				new TimeValue('+00000001952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, '')
			)
		);
	}

	public function testFormatterFormatWikibaseItem() {
		$this->assertEquals(
			new StringResourceNode('Douglas Adams'),
			$this->newFactory()->newWikibaseValueFormatter()->format(new EntityIdValue(new ItemId('Q42')))
		);
	}

	public function testFormatterFormatWikibaseProperty() {
		$this->assertEquals(
			new StringResourceNode('date de naissance'),
			$this->newFactory()->newWikibaseValueFormatter()->format(new EntityIdValue(new PropertyId('P569')))
		);
	}
}
