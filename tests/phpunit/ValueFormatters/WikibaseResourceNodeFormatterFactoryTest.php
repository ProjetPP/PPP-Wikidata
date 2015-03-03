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
use PPP\DataModel\JsonLdResourceNode;
use PPP\DataModel\StringResourceNode;
use PPP\Wikidata\WikibaseResourceNode;
use stdClass;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\EntityStore\InMemory\InMemoryEntityStore;

/**
 * @covers PPP\Wikidata\ValueFormatters\WikibaseResourceNodeFormatterFactory
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseResourceNodeFormatterFactoryTest extends \PHPUnit_Framework_TestCase {

	private function newFactory() {
		$entityStore = new InMemoryEntityStore(array(
			$this->getQ42(),
			$this->getP214()
		));

		return new WikibaseResourceNodeFormatterFactory('en', $entityStore, array(), new ArrayCache());
	}

	public function testFormatterFormatGlobeCoordinate() {
		$this->assertEquals(
			new JsonLdResourceNode(
				'42, 42',
				(object) array(
					'@context' => 'http://schema.org',
					'@type' => 'GeoCoordinates',
					'name' => '42, 42',
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
			new JsonLdResourceNode(
				'foo',
				(object) array(
					'@context' => 'http://schema.org',
					'@type' => 'Text',
					'http://www.w3.org/1999/02/22-rdf-syntax-ns#value' => (object) array(
						'@language' => 'en',
						'@value' => 'foo'
					)
				)
			),
			$this->newFactory()->newWikibaseResourceNodeFormatter()->format(
				new WikibaseResourceNode('', new MonolingualTextValue('en', 'foo'))
			)
		);
	}

	public function testFormatterFormatQuantity() {
		$this->assertEquals(
			new JsonLdResourceNode(
				'1234.0±1.0',
				(object) array(
					'@context' => 'http://schema.org',
					'@type' => 'QuantitativeValue',
					'name' => '1234.0±1.0',
					'value' => (object) array('@type' => 'Integer', '@value' => 1234),
					'minValue' => (object) array('@type' => 'Float', '@value' => 1233.3333),
					'maxValue' => (object) array('@type' => 'Integer', '@value' => 1235),
				)
			),
			$this->newFactory()->newWikibaseResourceNodeFormatter()->format(
				new WikibaseResourceNode('', new QuantityValue(new DecimalValue(1234), '1', new DecimalValue(1235), new DecimalValue(1233.3333)))
			)
		);
	}

	public function testFormatterFormatString() {
		$this->assertEquals(
			new JsonLdResourceNode(
				'foo',
				(object) array(
					'@context' => 'http://schema.org',
					'@type' => 'Text',
					'http://www.w3.org/1999/02/22-rdf-syntax-ns#value' => (object) array(
						'@value' => 'foo'
					)
				)
			),
			$this->newFactory()->newWikibaseResourceNodeFormatter()->format(
				new WikibaseResourceNode('', new StringValue('foo'))
			)
		);
	}

	public function testFormatterFormatTime() {
		$this->assertEquals(
			new JsonLdResourceNode(
				'1952-03-11',
				(object) array(
					'@context' => 'http://schema.org',
					'@type' => 'Date',
					'http://www.w3.org/1999/02/22-rdf-syntax-ns#value' => (object) array(
						'@type' => 'Date',
						'@value' => '1952-03-11'
					)
				)
			),
			$this->newFactory()->newWikibaseResourceNodeFormatter()->format(
				new WikibaseResourceNode('', new TimeValue('+00000001952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, ''))
			)
		);
	}

	public function testFormatterFormatUnknown() {
		$this->assertEquals(
			new JsonLdResourceNode(
				'foo',
				(object) array(
					'@context' => 'http://schema.org',
					'@type' => 'Text',
					'http://www.w3.org/1999/02/22-rdf-syntax-ns#value' => (object) array(
						'@value' => 'foo'
					)
				)
			),
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
							'image' => '//upload.wikimedia.org/wikipedia/commons/f/ff/Wikidata-logo.svg',
							'target' => '//www.wikidata.org/entity/Q42'
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
					'@type' => 'Property',
					'@id' => 'http://www.wikidata.org/entity/P214',
					'name' => (object) array('@value' => 'VIAF identifier', '@language' => 'en'),
					'@reverse' => new stdClass()
				)
			),
			$this->newFactory()->newWikibaseResourceNodeFormatter()->format(
				new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214')))
			)
		);
	}

	private function getQ42() {
		$item = new Item();
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
