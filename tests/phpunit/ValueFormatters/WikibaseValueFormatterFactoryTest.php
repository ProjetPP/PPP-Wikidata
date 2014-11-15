<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\DecimalValue;
use DataValues\GlobeCoordinateValue;
use DataValues\LatLongValue;
use DataValues\MonolingualTextValue;
use DataValues\QuantityValue;
use DataValues\StringValue;
use DataValues\TimeValue;
use DataValues\UnknownValue;
use Doctrine\Common\Cache\ArrayCache;
use Mediawiki\Api\MediawikiApi;
use PPP\DataModel\StringResourceNode;
use PPP\DataModel\TimeResourceNode;
use PPP\Wikidata\Cache\WikibaseEntityCache;
use PPP\Wikidata\DataModel\WikibaseEntityResourceNode;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers PPP\Wikidata\ValueFormatters\WikibaseValueFormatterFactory
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseValueFormatterFactoryTest extends \PHPUnit_Framework_TestCase {

	private function newFactory() {
		$cache = new ArrayCache();
		$entityCache = new WikibaseEntityCache($cache);
		$entityCache->save($this->getQ42());
		$entityCache->save($this->getP214());

		return new WikibaseValueFormatterFactory('en', new MediawikiApi(''), $cache);
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
			new TimeResourceNode('1952-03-11'),
			$this->newFactory()->newWikibaseValueFormatter()->format(
				new TimeValue('+00000001952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, '')
			)
		);
	}

	public function testFormatterFormatUnknown() {
		$this->assertEquals(
			new StringResourceNode('foo'),
			$this->newFactory()->newWikibaseValueFormatter()->format(new UnknownValue('foo'))
		);
	}

	public function testFormatterFormatWikibaseItem() {
		$this->assertEquals(
			new WikibaseEntityResourceNode('Douglas Adams', new ItemId('Q42')),
			$this->newFactory()->newWikibaseValueFormatter()->format(new EntityIdValue(new ItemId('Q42')))
		);
	}

	public function testFormatterFormatWikibaseProperty() {
		$this->assertEquals(
			new WikibaseEntityResourceNode('VIAF identifier', new PropertyId('P214')),
			$this->newFactory()->newWikibaseValueFormatter()->format(new EntityIdValue(new PropertyId('P214')))
		);
	}

	private function getQ42() {
		$item = Item::newEmpty();
		$item->setId( new ItemId('Q42'));
		$item->getFingerprint()->setLabel('en', 'Douglas Adams');

		return $item;
	}

	private function getP214() {
		$property = Property::newFromType('string');
		$property->setId(new PropertyId('P214'));
		$property->getFingerprint()->setLabel('en', 'VIAF identifier');

		return $property;
	}
}
