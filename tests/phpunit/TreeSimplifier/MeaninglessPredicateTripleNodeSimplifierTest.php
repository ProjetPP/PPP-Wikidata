<?php

namespace PPP\Wikidata;

use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\StringResourceNode;
use PPP\DataModel\TripleNode;
use PPP\DataModel\UnionNode;
use PPP\Module\TreeSimplifier\NodeSimplifierBaseTest;
use PPP\Wikidata\TreeSimplifier\MeaninglessPredicateTripleNodeSimplifier;

/**
 * @covers PPP\Wikidata\TreeSimplifier\MeaninglessPredicateTripleNodeSimplifier
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class MeaninglessPredicateTripleNodeSimplifierTest extends NodeSimplifierBaseTest {

	protected function buildSimplifier() {
		return new MeaninglessPredicateTripleNodeSimplifier();
	}

	public function simplifiableProvider() {
		return array(
			array(
				new TripleNode(new MissingNode(), new ResourceListNode(), new MissingNode())
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
				new TripleNode(
					new ResourceListNode(array(new StringResourceNode('a'))),
					new ResourceListNode(array(new StringResourceNode('foo'))),
					new MissingNode()
				),
				new TripleNode(
					new ResourceListNode(array(new StringResourceNode('a'))),
					new ResourceListNode(array(new StringResourceNode('foo'))),
					new MissingNode()
				)
			),
			array(
				new ResourceListNode(array(new StringResourceNode('a'))),
				new TripleNode(
					new ResourceListNode(array(new StringResourceNode('a'))),
					new ResourceListNode(array(new StringResourceNode('name'))),
					new MissingNode()
				)
			),
			array(
				new UnionNode(array(
					new ResourceListNode(array(new StringResourceNode('a'))),
					new TripleNode(
						new ResourceListNode(array(new StringResourceNode('a'))),
						new ResourceListNode(array(new StringResourceNode('foo'))),
						new MissingNode()
					)
				)),
				new TripleNode(
					new ResourceListNode(array(new StringResourceNode('a'))),
					new ResourceListNode(array(new StringResourceNode('name'), new StringResourceNode('foo'))),
					new MissingNode()
				)
			),
		);
	}
}
