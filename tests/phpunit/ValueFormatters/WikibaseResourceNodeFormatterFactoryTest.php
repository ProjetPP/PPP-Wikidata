<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\DecimalValue;
use DataValues\Geo\Values\GlobeCoordinateValue;
use DataValues\Geo\Values\LatLongValue;
use DataValues\MonolingualTextValue;
use DataValues\QuantityValue;
use DataValues\StringValue;
use DataValues\TimeValue;
use DataValues\UnknownValue;
use Doctrine\Common\Cache\ArrayCache;
use PPP\DataModel\JsonLdResourceNode;
use PPP\Wikidata\WikibaseResourceNode;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\Term;
use Wikibase\DataModel\Term\TermList;
use Wikibase\EntityStore\InMemory\InMemoryEntityStore;

/**
 * @covers PPP\Wikidata\ValueFormatters\WikibaseResourceNodeFormatterFactory
 *
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class WikibaseResourceNodeFormatterFactoryTest extends \PHPUnit_Framework_TestCase {

	private function newFactory() {
		$entityStore = new InMemoryEntityStore(array(
			$this->getQ42(),
			$this->getP214(),
			$this->getP625()
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
					'longitude' => 42.0,
					'@reverse' => (object) array(
						'geo' => array(
							(object) array(
								'@type' => 'Thing',
								'@id' => 'http://www.wikidata.org/entity/Q42',
								'name' => (object) array('@value' => 'Douglas Adams', '@language' => 'en'),
								'potentialAction' => array(
									(object) array(
										'@type' => 'ViewAction',
										'name' => array(
											(object) array('@value' => 'View on Wikidata', '@language' => 'en'),
											(object) array('@value' => 'Voir sur Wikidata', '@language' => 'fr')
										),
										'image' => '//upload.wikimedia.org/wikipedia/commons/f/ff/Wikidata-logo.svg',
										'target' => '//www.wikidata.org/entity/Q42'
									)
								)
							)
						)
					)
				)
			),
			$this->newFactory()->newWikibaseResourceNodeFormatter()->format(
				new WikibaseResourceNode(
					'',
					new GlobeCoordinateValue(new LatLongValue(42, 42), 1),
					new ItemId('Q42'),
					new PropertyId('P625')
				)
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
				new WikibaseResourceNode('', new TimeValue('+1952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, 'http://www.wikidata.org/entity/Q1985786'))
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
					'name' => (object) array('@value' => 'Douglas Adams', '@language' => 'en'),
					'potentialAction' => array(
						(object) array(
							'@type' => 'ViewAction',
							'name' => array(
								(object) array('@value' => 'View on Wikidata', '@language' => 'en'),
								(object) array('@value' => 'Voir sur Wikidata', '@language' => 'fr')
							),
							'image' => '//upload.wikimedia.org/wikipedia/commons/f/ff/Wikidata-logo.svg',
							'target' => '//www.wikidata.org/entity/Q42'
						)
					)
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
					'name' => (object) array('@value' => 'VIAF identifier', '@language' => 'en')
				)
			),
			$this->newFactory()->newWikibaseResourceNodeFormatter()->format(
				new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214')))
			)
		);
	}

	private function getQ42() {
		return new Item(
			new ItemId('Q42'),
			new Fingerprint(new TermList(array(new Term('en', 'Douglas Adams'))))
		);
	}

	private function getP214() {
		return new Property(
			new PropertyId('P214'),
			new Fingerprint(new TermList(array(new Term('en', 'VIAF identifier')))),
			'string'
		);
	}

	private function getP625() {
		return new Property(
			new PropertyId('P625'),
			new Fingerprint(new TermList(array(new Term('en', 'geo coordinates')))),
			'globe-coordinate',
			new StatementList(array(
				new Statement(new PropertyValueSnak(new PropertyId('P1628'), new StringValue('http://schema.org/geo')))
			))
		);
	}
}
