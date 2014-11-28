<?php

namespace PPP\Wikidata\TreeSimplifier;

/**
 * @licence MIT
 * @author Thomas Pellissier Tanon
 */
abstract class NodeSimplifierBaseTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @return NodeSimplifier
	 */
	protected abstract function buildSimplifier();

	public function testImplementsNodeSimplifierInterface() {
		$this->assertInstanceOf('PPP\Module\TreeSimplifier\NodeSimplifier', $this->buildSimplifier());
	}

	/**
	 * @dataProvider simplifiableProvider
	 */
	public function testIsNodeSimplifierForReturnsTrue($simplifiable) {
		$this->assertTrue($this->buildSimplifier()->isSimplifierFor($simplifiable));
	}
	
	public abstract function simplifiableProvider();

	/**
	 * @dataProvider nonSimplifiableProvider
	 */
	public function testIsNodeSimplifierForReturnsFalse($nonSimplifiable) {
		$this->assertFalse($this->buildSimplifier()->isSimplifierFor($nonSimplifiable));
	}

	/**
	 * @dataProvider nonSimplifiableProvider
	 */
	public function testSimplificationThrowsUnsupportedObjectException($nonSimplifiable) {
		$this->setExpectedException('InvalidArgumentException');
		$this->buildSimplifier()->simplify($nonSimplifiable);
	}

	/**
	 * @return array
	 */
	public abstract function nonSimplifiableProvider();
}
