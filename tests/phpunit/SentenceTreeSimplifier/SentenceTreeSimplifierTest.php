<?php

namespace PPP\Wikidata\SentenceTreeSimplifier;

use PPP\DataModel\MissingNode;
use PPP\DataModel\StringResourceNode;

/**
 * @covers PPP\Wikidata\SentenceTreeSimplifier\SentenceTreeSimplifier
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class SentenceTreeSimplifierTest extends \PHPUnit_Framework_TestCase {

	public function testSimplify() {
		$nodeSimplifierMock = $this->getMock('PPP\Wikidata\SentenceTreeSimplifier\NodeSimplifier');
		$nodeSimplifierMock->expects($this->any())
			->method('isSimplifierFor')
			->with($this->equalTo(new MissingNode()))
			->will($this->returnValue(true));
		$nodeSimplifierMock->expects($this->any())
			->method('simplify')
			->with($this->equalTo(new MissingNode()))
			->will($this->returnValue(array(new StringResourceNode('foo'))));

		$simplifier = new SentenceTreeSimplifier(array($nodeSimplifierMock));
		$this->assertEquals(
			array(new StringResourceNode('foo')),
			$simplifier->simplify(new MissingNode())
		);
	}
}
