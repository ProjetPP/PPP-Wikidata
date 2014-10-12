<?php

namespace PPP\Wikidata\SentenceTreeSimplifier;

use PPP\DataModel\AbstractNode;

/**
 * @licence GNU GPL v2+
 * @author Thomas Pellissier Tanon
 */
abstract class NodeSimplifierBaseTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @return NodeSimplifier
	 */
	public abstract function buildDummySimplifier();

	public function testNodeSimplifierInterface() {
		$this->assertInstanceOf('PPP\Wikidata\SentenceTreeSimplifier\NodeSimplifier', $this->buildDummySimplifier());
	}

	/**
	 * @dataProvider simplifiableProvider
	 */
	public function testIsSimplifierForReturnsTrue(AbstractNode $node) {
		$this->assertTrue($this->buildDummySimplifier()->isSimplifierFor($node));
	}

	/**
	 * @return AbstractNode[] node that are simplifiable by the simplifier
	 */
	public abstract function simplifiableProvider();

	/**
	 * @dataProvider nonSimplifiableProvider
	 */
	public function testIsSimplifierForReturnsFalse(AbstractNode $node) {
		$this->assertFalse($this->buildDummySimplifier()->isSimplifierFor($node));
	}

	/**
	 * @dataProvider nonSimplifiableProvider
	 */
	public function testSimplifyThrowsInvalidArgumentException(AbstractNode $node) {
		$this->setExpectedException('InvalidArgumentException');
		$this->buildDummySimplifier()->simplify($node);
	}

	/**
	 * @return AbstractNode[] node that are not simplifiable by the simplifier
	 */
	public abstract function nonSimplifiableProvider();
}
