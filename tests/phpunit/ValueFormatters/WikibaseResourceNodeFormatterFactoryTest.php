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
use PPP\DataModel\JsonLdResourceNode;
use PPP\DataModel\StringResourceNode;
use PPP\DataModel\TimeResourceNode;
use PPP\Wikidata\Cache\WikibaseEntityCache;
use PPP\Wikidata\WikibaseResourceNode;
use stdClass;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers PPP\Wikidata\ValueFormatters\WikibaseResourceNodeFormatterFactory
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseResourceNodeFormatterFactoryTest extends \PHPUnit_Framework_TestCase {

	private function newFactory() {
		$cache = new ArrayCache();
		$entityCache = new WikibaseEntityCache($cache);
		$entityCache->save($this->getQ42());
		$entityCache->save($this->getP214());

		return new WikibaseResourceNodeFormatterFactory('en', new MediawikiApi(''), array(), $cache);
	}

	public function testFormatterFormatGlobeCoordinate() {
		$this->assertEquals(
			new JsonLdResourceNode(
				'42, 42',
				(object) array(
					'@context' => 'http://schema.org',
					'@type' => 'GeoCoordinates',
					'latitude' => 42.0,
					'longitude' => 42.0
				)
			),
			$this->newFactory()->newWikibaseResourceNodeFormatter()->format(
				new WikibaseResourceNode('', new GlobeCoordinateValue(new LatLongValue(42, 42), 1))
			)
		);
	}

	public function testFormatterFormatMonolingualText() {
		$this->assertEquals(
			new StringResourceNode('foo', 'en'),
			$this->newFactory()->newWikibaseResourceNodeFormatter()->format(
				new WikibaseResourceNode('', new MonolingualTextValue('en', 'foo'))
			)
		);
	}

	public function testFormatterFormatQuantity() {
		$this->assertEquals(
			new StringResourceNode('491268±1'),
			$this->newFactory()->newWikibaseResourceNodeFormatter()->format(
				new WikibaseResourceNode('', new QuantityValue(new DecimalValue('+491268'), '1', new DecimalValue('+491268'), new DecimalValue('+491267')))
			)
		);
	}

	public function testFormatterFormatString() {
		$this->assertEquals(
			new StringResourceNode('foo'),
			$this->newFactory()->newWikibaseResourceNodeFormatter()->format(
				new WikibaseResourceNode('', new StringValue('foo'))
			)
		);
	}

	public function testFormatterFormatTime() {
		$this->assertEquals(
			new TimeResourceNode('1952-03-11'),
			$this->newFactory()->newWikibaseResourceNodeFormatter()->format(
				new WikibaseResourceNode('', new TimeValue('+00000001952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, ''))
			)
		);
	}

	public function testFormatterFormatUnknown() {
		$this->assertEquals(
			new StringResourceNode('foo'),
			$this->newFactory()->newWikibaseResourceNodeFormatter()->format(
				new WikibaseResourceNode('', new UnknownValue('foo'))
			)
		);
	}

	public function testFormatterFormatWikibaseItem() {
		$this->assertEquals(
			new JsonLdResourceNode(
				'Douglas Adams',
				(object) array(
					'@context' => 'http://schema.org',
					'@type' => 'Thing',
					'@id' => 'http://www.wikidata.org/entity/Q42',
					'name' => (object) array('@value' => 'Douglas Adams', '@language' => 'en'),'potentialAction' => array(
						(object) array(
							'@type' => 'ViewAction',
							'name' => array(
								(object) array('@value' => 'View on Wikidata', '@language' => 'en'),
								(object) array('@value' => 'Voir sur Wikidata', '@language' => 'fr')
							),
							'image' => 'https://upload.wikimedia.org/wikipedia/commons/f/ff/Wikidata-logo.svg',
							'target' => 'https://www.wikidata.org/entity/Q42'
						)
					),
					'@reverse' => new stdClass()
				)
			),
			$this->newFactory()->newWikibaseResourceNodeFormatter()->format(
				new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42')))
			)
		);
	}

	public function testFormatterFormatWikibaseProperty() {
		$this->assertEquals(
			new JsonLdResourceNode(
				'VIAF identifier',
				(object) array(
					'@context' => 'http://schema.org',
					'@type' => 'Thing',
					'@id' => 'http://www.wikidata.org/entity/P214',
					'name' => (object) array('@value' => 'VIAF identifier', '@language' => 'en'),
					'potentialAction' => array(
						(object) array(
							'@type' => 'ViewAction',
							'name' => array(
								(object) array('@value' => 'View on Wikidata', '@language' => 'en'),
								(object) array('@value' => 'Voir sur Wikidata', '@language' => 'fr')
							),
							'image' => 'https://upload.wikimedia.org/wikipedia/commons/f/ff/Wikidata-logo.svg',
							'target' => 'https://www.wikidata.org/entity/P214'
						)
					),
					'@reverse' => new stdClass()
				)
			),
			$this->newFactory()->newWikibaseResourceNodeFormatter()->format(
				new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214')))
			)
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

	public function testWikibaseEntityIdFormatterPreloader() {
		$this->assertInstanceOf(
			'PPP\Wikidata\ValueFormatters\WikibaseEntityIdFormatterPreloader',
			$this->newFactory()->newWikibaseEntityIdFormatterPreloader()
		);
	}
}
