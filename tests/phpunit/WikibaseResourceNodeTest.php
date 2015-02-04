<?php

namespace PPP\Wikidata;

use DataValues\BooleanValue;
use DataValues\StringValue;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceNode;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

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

	public function testGetFromSubject() {
		$node = new WikibaseResourceNode('foo', new StringValue('foo'), new ItemId('Q42'), new PropertyId('P42'));
		$this->assertEquals(new ItemId('Q42'), $node->getFromSubject());
	}

	public function testGetFromSubjectDefault() {
		$node = new WikibaseResourceNode('foo', new StringValue('foo'));
		$this->assertEquals(null, $node->getFromSubject());
	}

	public function testGetFromPredicate() {
		$node = new WikibaseResourceNode('foo', new StringValue('foo'), new ItemId('Q42'), new PropertyId('P42'));
		$this->assertEquals(new PropertyId('P42'), $node->getFromPredicate());
	}

	public function testGetFromPredicateDefault() {
		$node = new WikibaseResourceNode('foo', new StringValue('foo'));
		$this->assertEquals(null, $node->getFromPredicate());
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
