<?php

namespace PPP\Wikidata;

use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\StringResourceNode;
use PPP\DataModel\TripleNode;
use PPP\Module\TreeSimplifier\NodeSimplifierBaseTest;

/**
 * @covers PPP\Wikidata\WikidataTripleNodeCleaner
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikidataTripleNodeCleanerTest extends NodeSimplifierBaseTest {

	protected function buildSimplifier() {
		return new WikidataTripleNodeCleaner();
	}

	public function simplifiableProvider() {
		return array(
			array(
				new TripleNode(new MissingNode(), new MissingNode(), new MissingNode())
			)
		);
	}

	public function nonSimplifiableProvider() {
		return array(
			array(
				new MissingNode()
			)
		);
	}

	public function simplificationProvider() {
		return array(
			array(
				new TripleNode(new MissingNode(), new MissingNode(), new MissingNode()),
				new TripleNode(new MissingNode(), new MissingNode(), new MissingNode())
			),
			array(
				new ResourceListNode(array(new StringResourceNode('a'))),
				new TripleNode(
					new ResourceListNode(array(new StringResourceNode('a'))),
					new ResourceListNode(array(new StringResourceNode('name'))),
					new MissingNode()
				)
			),
		);
	}
}
