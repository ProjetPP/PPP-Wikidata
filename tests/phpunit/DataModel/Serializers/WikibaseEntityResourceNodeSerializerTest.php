<?php

namespace PPP\Wikidata\DataModel\Serializers;

use PPP\DataModel\BooleanResourceNode;
use PPP\DataModel\MissingNode;
use PPP\Wikidata\DataModel\WikibaseEntityResourceNode;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers PPP\Wikidata\DataModel\Serializers\WikibaseEntityResourceNodeSerializer
 *
 * @licence MIT
 * @author Thomas Pellissier Tanon
 */
class WikibaseEntityResourceNodeSerializerTest extends \PHPUnit_Framework_TestCase {

	public function buildSerializer() {
		return new WikibaseEntityResourceNodeSerializer();
	}

	public function testIsSerializerForReturnsTrue() {
		$this->assertTrue($this->buildSerializer()->isSerializerFor(new WikibaseEntityResourceNode('a', new ItemId('Q42'))));
	}

	/**
	 * @dataProvider nonSerializableProvider
	 */
	public function testIsSerializerForReturnsFalse($nonSerializable) {
		$this->assertFalse($this->buildSerializer()->isSerializerFor($nonSerializable));
	}

	/**
	 * @dataProvider nonSerializableProvider
	 */
	public function testSerializeThrowsUnsupportedObjectException($nonSerializable) {
		$this->setExpectedException('Serializers\Exceptions\UnsupportedObjectException');
		$this->buildSerializer()->serialize($nonSerializable);
	}

	public function nonSerializableProvider() {
		return array(
			array(
				42
			),
			array(
				new MissingNode()
			),
			array(
				new BooleanResourceNode('true')
			)
		);
	}

	/**
	 * @dataProvider serializationProvider
	 */
	public function testSerialization($serialization, $object) {
		$this->assertEquals(
			$serialization,
			$this->buildSerializer()->serialize($object)
		);
	}

	public function serializationProvider() {
		return array(
			array(
				array(
					'type' => 'resource',
					'value' => 'a',
					'value-type' => 'wikibase-entity',
					'entity-id' => 'Q42'
				),
				new WikibaseEntityResourceNode('a', new ItemId('Q42'))
			),
			array(
				array(
					'type' => 'resource',
					'value' => 'a',
					'value-type' => 'wikibase-entity',
					'entity-id' => 'Q42',
					'description' => 'foo'
				),
				new WikibaseEntityResourceNode('a', new ItemId('Q42'), 'foo')
			)
		);
	}
}
