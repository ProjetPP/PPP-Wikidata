<?php

namespace PPP\Wikidata\DataModel\Deserializers;

use PPP\Wikidata\DataModel\WikibaseEntityResourceNode;
use Wikibase\DataModel\Entity\BasicEntityIdParser;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers PPP\Wikidata\DataModel\Deserializers\WikibaseEntityResourceNodeDeserializer
 *
 * @licence MIT
 * @author Thomas Pellissier Tanon
 */
class WikibaseEntityResourceNodeDeserializerTest extends \PHPUnit_Framework_TestCase {

	public function buildDeserializer() {
		return new WikibaseEntityResourceNodeDeserializer(new BasicEntityIdParser());
	}

	public function testIsDeserializerForReturnsTrue() {
		$this->assertTrue($this->buildDeserializer()->isDeserializerFor(array(
			'type' => 'resource',
			'value-type' => 'wikibase-entity',
			'value' => 'foo',
			'entity-id' => 'Q42'
		)));
	}

	/**
	 * @dataProvider nonDeserializableProvider
	 */
	public function testIsDeserializerForReturnsFalse($nonSerializable) {
		$this->assertFalse($this->buildDeserializer()->isDeserializerFor($nonSerializable));
	}

	/**
	 * @dataProvider nonDeserializableProvider
	 */
	public function testSerializeThrowsUnsupportedObjectException($nonDeserializable) {
		$this->setExpectedException('Deserializers\Exceptions\DeserializationException');
		$this->buildDeserializer()->deserialize($nonDeserializable);
	}

	public function nonDeserializableProvider() {
		return array(
			array(
				42
			),
			array(
				array(
					'type' => 'foo'
				)
			),
			array(
				array(
					'type' => 'resource',
					'value-type' => 'boolean',
					'value' => 'true'
				)
			)
		);
	}

	public function testDeserialization() {
		$this->assertEquals(
			new WikibaseEntityResourceNode('a', new ItemId('Q42')),
			$this->buildDeserializer()->deserialize(array(
				'type' => 'resource',
				'value' => 'a',
				'value-type' => 'wikibase-entity',
				'entity-id' => 'Q42'
			))
		);
	}
}
