<?php

namespace PPP\Wikidata\TreeSimplifier;

use Doctrine\Common\Cache\ArrayCache;
use Mediawiki\Api\MediawikiApi;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\StringResourceNode;
use PPP\DataModel\TripleNode;
use PPP\DataModel\UnionNode;
use PPP\Module\TreeSimplifier\NodeSimplifierBaseTest;
use PPP\Wikidata\ValueParsers\ResourceListNodeParser;
use PPP\Wikidata\ValueParsers\WikibaseValueParserFactory;
use PPP\Wikidata\WikibaseResourceNode;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers PPP\Wikidata\TreeSimplifier\MeaninglessPredicateTripleNodeSimplifier
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class MeaninglessPredicateTripleNodeSimplifierTest extends NodeSimplifierBaseTest {

	protected function buildSimplifier() {
		$valueParserFactory = new WikibaseValueParserFactory(
			'en',
			new MediawikiApi('http://www.wikidata.org/w/api.php'),
			new ArrayCache()
		);

		return new MeaninglessPredicateTripleNodeSimplifier(new ResourceListNodeParser($valueParserFactory->newWikibaseValueParser()));
	}

	public function simplifiableProvider() {
		return array(
			array(
				new TripleNode(new ResourceListNode(), new ResourceListNode(), new MissingNode())
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
				new ResourceListNode(array(new WikibaseResourceNode('Douglas Adams', new EntityIdValue(new ItemId('Q42'))))),
				new TripleNode(
					new ResourceListNode(array(new StringResourceNode('Douglas Adams'))),
					new ResourceListNode(array(new StringResourceNode('name'))),
					new MissingNode()
				)
			),
			array(
				new ResourceListNode(array(new WikibaseResourceNode('P=NP', new EntityIdValue(new ItemId('Q746242'))))),
				new TripleNode(
					new ResourceListNode(array(new StringResourceNode('P=NP'))),
					new ResourceListNode(array(new StringResourceNode('definition'))),
					new MissingNode()
				)
			),
			array(
				new UnionNode(array(
					new ResourceListNode(array(new WikibaseResourceNode('Douglas Adams', new EntityIdValue(new ItemId('Q42'))))),
					new TripleNode(
						new ResourceListNode(array(new StringResourceNode('Douglas Adams'))),
						new ResourceListNode(array(new StringResourceNode('foo'))),
						new MissingNode()
					)
				)),
				new TripleNode(
					new ResourceListNode(array(new StringResourceNode('Douglas Adams'))),
					new ResourceListNode(array(new StringResourceNode('name'), new StringResourceNode('foo'))),
					new MissingNode()
				)
			),
		);
	}
}
