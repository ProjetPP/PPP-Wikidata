<?php

namespace PPP\Wikidata;

use DataValues\StringValue;

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
}
