<?php

namespace PPP\Wikidata;

use DataValues\BooleanValue;
use DataValues\StringValue;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceNode;

/**
 * @covers PPP\Wikidata\WikibaseResourceNode
 *
 * @licence MIT
 * @author Thomas Pellissier Tanon
 */
class ResourceNodeTest extends \PHPUnit_Framework_TestCase {

	public function testGetDataValue() {
		$node = new WikibaseResourceNode('foo', new StringValue('foo'));
		$this->assertEquals(new StringValue('foo'), $node->getDataValue());
	}

	public function testGetValueType() {
		$node = new WikibaseResourceNode('foo', new StringValue('foo'));
		$this->assertEquals('wikidata-datavalue', $node->getValueType());
	}

	public function testEquals() {
		$node = new WikibaseResourceNode('foo ', new StringValue('foo'));
		$this->assertTrue($node->equals(new WikibaseResourceNode('foo', new StringValue('foo'))));
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
				new WikibaseResourceNode('foo', new StringValue('foo')),
				new MissingNode()
			),
			array(
				new WikibaseResourceNode('true', new StringValue('true')),
				new WikibaseResourceNode('true', new BooleanValue(true))
			),
		);
	}
}
