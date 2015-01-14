<?php

namespace PPP\Wikidata\TreeSimplifier;

use Doctrine\Common\Cache\ArrayCache;
use Mediawiki\Api\MediawikiApi;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\IntersectionNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\StringResourceNode;
use PPP\DataModel\TripleNode;
use PPP\DataModel\UnionNode;
use PPP\Wikidata\ValueParsers\ResourceListNodeParser;
use PPP\Wikidata\ValueParsers\WikibaseValueParserFactory;
use PPP\Wikidata\WikibaseResourceNode;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers PPP\Wikidata\TreeSimplifier\SpecificTripleNodeSimplifier
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class SpecificTripleNodeSimplifierTest extends NodeSimplifierBaseTest {

	protected function buildSimplifier() {
		$valueParserFactory = new WikibaseValueParserFactory(
			'en',
			new MediawikiApi('http://www.wikidata.org/w/api.php'),
			new ArrayCache()
		);

		return new SpecificTripleNodeSimplifier(new ResourceListNodeParser($valueParserFactory->newWikibaseValueParser()));
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

	/**
	 * @dataProvider simplifiedTripleProvider
	 */
	public function testSimplify(AbstractNode $outputNode, TripleNode $inputNode) {
		$resourceListNodeParserMock = $this->getMockBuilder('PPP\Wikidata\ValueParsers\ResourceListNodeParser')
			->disableOriginalConstructor()
			->getMock();
		$resourceListNodeParserMock->expects($this->any())
			->method('parse')
			->with($this->equalTo(new ResourceListNode(array(new StringResourceNode('Douglas Adams')))))
			->will($this->returnValue(new ResourceListNode(array(new WikibaseResourceNode('Douglas Adams', new EntityIdValue(new ItemId('Q42')))))));

		$simplifier = new SpecificTripleNodeSimplifier($resourceListNodeParserMock);

		$this->assertEquals(
			$outputNode,
			$simplifier->simplify($inputNode)
		);
	}

	public function simplifiedTripleProvider() {
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
				new ResourceListNode(array(new WikibaseResourceNode('Douglas Adams', new EntityIdValue(new ItemId('Q42'))))),
				new TripleNode(
					new ResourceListNode(array(new StringResourceNode('Douglas Adams'))),
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
			array(
				new IntersectionNode(array(
					new TripleNode(
						new ResourceListNode(array(new StringResourceNode('Douglas Adams'))),
						new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P40'))))),
						new MissingNode()
					),
					new TripleNode(
						new MissingNode(),
						new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P21'))))),
						new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q6581097')))))
					)
				)),
				new TripleNode(
					new ResourceListNode(array(new StringResourceNode('Douglas Adams'))),
					new ResourceListNode(array(new StringResourceNode('son'))),
					new MissingNode()
				)
			),
			array(
				new IntersectionNode(array(
					new TripleNode(
						new ResourceListNode(array(new StringResourceNode('Douglas Adams'))),
						new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P40'))))),
						new MissingNode()
					),
					new TripleNode(
						new MissingNode(),
						new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P21'))))),
						new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q6581072')))))
					)
				)),
				new TripleNode(
					new ResourceListNode(array(new StringResourceNode('Douglas Adams'))),
					new ResourceListNode(array(new StringResourceNode('daughter'))),
					new MissingNode()
				)
			),
		);
	}
}
