<?php

namespace PPP\Wikidata\TreeSimplifier;

use DataValues\TimeValue;
use DateTime;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\IntersectionNode;
use PPP\DataModel\JsonLdResourceNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\StringResourceNode;
use PPP\DataModel\TripleNode;
use PPP\DataModel\UnionNode;
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
		$resourceListNodeParserMock = $this->getMockBuilder('PPP\Wikidata\ValueParsers\ResourceListNodeParser')
			->disableOriginalConstructor()
			->getMock();
		$resourceListForEntityPropertyMock = $this->getMockBuilder('PPP\Wikidata\TreeSimplifier\ResourceListForEntityProperty')
			->disableOriginalConstructor()
			->getMock();

		date_default_timezone_set('UTC');
		return new SpecificTripleNodeSimplifier($resourceListNodeParserMock, $resourceListForEntityPropertyMock, new DateTime('2015-03-12'));
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
		$resourceListForEntityPropertyMock = $this->getMockBuilder('PPP\Wikidata\TreeSimplifier\ResourceListForEntityProperty')
			->disableOriginalConstructor()
			->getMock();
		$resourceListForEntityPropertyMock->expects($this->any())
			->method('getForEntityProperty')
			->with($this->equalTo(new ItemId('Q42')), $this->equalTo('P569'))
			->will($this->returnValue(new ResourceListNode(array(new WikibaseResourceNode('', new TimeValue('+00000001952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, 'http://www.wikidata.org/entity/Q1985786'))))));

		date_default_timezone_set('UTC');
		$simplifier = new SpecificTripleNodeSimplifier($resourceListNodeParserMock, $resourceListForEntityPropertyMock, new DateTime('2015-03-12'));

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
			array(
				new ResourceListNode(array(
					new JsonLdResourceNode(
						'63',
						(object) array(
							'@context' => 'http://schema.org',
							'@type' => 'Duration',
							'name' => '63',
							'http://www.w3.org/1999/02/22-rdf-syntax-ns#value' => (object) array(
								'@type' => 'Duration',
								'@value' => 'P63Y0M1D'
							)
						)
					)
				)),
				new TripleNode(
					new ResourceListNode(array(new StringResourceNode('Douglas Adams'))),
					new ResourceListNode(array(new StringResourceNode('age'))),
					new MissingNode()
				)
			),
		);
	}
}
