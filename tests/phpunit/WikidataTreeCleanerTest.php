<?php

namespace PPP\Wikidata;

use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\StringResourceNode;
use PPP\DataModel\TripleNode;

/**
 * @covers PPP\Wikidata\WikidataTreeCleaner
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikidataTreeCleanerTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider cleanTreeProvider
	 */
	public function testClean(AbstractNode $originalTree, AbstractNode $newTree) {
		$cleaner = new WikidataTreeCleaner();
		$this->assertEquals($newTree, $cleaner->clean($originalTree));
	}

	public function cleanTreeProvider() {
		return array(
			array(
				new MissingNode(),
				new MissingNode()
			),
			array(
				new StringResourceNode('a'),
				new StringResourceNode('a')
			),
			array(
				new TripleNode(new StringResourceNode('a'), new StringResourceNode('b'), new MissingNode()),
				new TripleNode(new StringResourceNode('a'), new StringResourceNode('b'), new MissingNode())
			),
			array(
				new TripleNode(new StringResourceNode('a'), new StringResourceNode('name'), new MissingNode()),
				new StringResourceNode('a')
			),
		);
	}
}
