<?php

namespace PPP\Wikidata\DataModel;

use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceNode;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers PPP\Wikidata\DataModel\WikibaseEntityResourceNode
 *
 * @licence MIT
 * @author Thomas Pellissier Tanon
 */
class TimeResourceNodeTest extends \PHPUnit_Framework_TestCase {

	public function testGetValue() {
		$node = new WikibaseEntityResourceNode('foo', new ItemId('Q42'));
		$this->assertEquals('foo', $node->getValue());
	}

	public function testGetEntityId() {
		$node = new WikibaseEntityResourceNode('', new ItemId('Q42'));
		$this->assertEquals(new ItemId('Q42'), $node->getEntityId());
	}

	public function testGetDescription() {
		$node = new WikibaseEntityResourceNode('', new ItemId('Q42'), 'foo');
		$this->assertEquals('foo', $node->getDescription());
	}

	public function testGetDefaultDescription() {
		$node = new WikibaseEntityResourceNode('', new ItemId('Q42'));
		$this->assertEquals('', $node->getDescription());
	}

	public function testGetValueType() {
		$node = new WikibaseEntityResourceNode('foo', new ItemId('Q42'));
		$this->assertEquals('wikibase-entity', $node->getValueType());
	}

	public function testGetType() {
		$node = new WikibaseEntityResourceNode('foo', new ItemId('Q42'));
		$this->assertEquals('resource', $node->getType());
	}

	public function testEquals() {
		$node = new WikibaseEntityResourceNode('foo', new ItemId('Q42'));
		$this->assertTrue($node->equals(new WikibaseEntityResourceNode('bar', new ItemId('Q42'))));
	}

	/**
	 * @dataProvider nonEqualsProvider
	 */
	public function testNonEquals(ResourceNode $node, $target) {
		$this->assertFalse($node->equals($target));
	}

	public function nonEqualsProvider() {
		return array(
			array(
				new WikibaseEntityResourceNode('foo', new ItemId('Q42')),
				new MissingNode()
			),
			array(
				new WikibaseEntityResourceNode('foo', new ItemId('Q42')),
				new WikibaseEntityResourceNode('foo', new ItemId('Q43'))
			),
		);
	}
}
