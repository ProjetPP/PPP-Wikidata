<?php

namespace PPP\Wikidata\TreeSimplifier;

use Ask\Language\Description\AnyValue;
use Ask\Language\Description\Conjunction;
use Ask\Language\Description\Description;
use Ask\Language\Description\Disjunction;
use Ask\Language\Description\SomeProperty;
use Ask\Language\Description\ValueDescription;
use Ask\Language\Option\QueryOptions;
use DataValues\StringValue;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\IntersectionNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\StringResourceNode;
use PPP\DataModel\TripleNode;
use PPP\DataModel\UnionNode;
use PPP\Module\TreeSimplifier\NodeSimplifierFactory;
use PPP\Wikidata\WikibaseResourceNode;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers PPP\Wikidata\TreeSimplifier\MissingSubjectTripleNodeSimplifier
 *
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class MissingSubjectTripleNodeSimplifierTest extends NodeSimplifierBaseTest {

	public function buildSimplifier() {
		$nodeSimplifierFactoryMock = $this->getMockBuilder('PPP\Module\TreeSimplifier\NodeSimplifierFactory')
			->disableOriginalConstructor()
			->getMock();
		$entityStoreMock = $this->getMock('Wikibase\EntityStore\EntityStore');
		$resourceListNodeParserMock = $this->getMockBuilder('PPP\Wikidata\ValueParsers\ResourceListNodeParser')
			->disableOriginalConstructor()
			->getMock();

		return new MissingSubjectTripleNodeSimplifier($nodeSimplifierFactoryMock, $entityStoreMock, $resourceListNodeParserMock);
	}

	public function simplifiableProvider() {
		return array(
			array(
				new TripleNode(
					new MissingNode(),
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
					new ResourceListNode(array(new WikibaseResourceNode('', new StringValue('113230702'))))
				)
			),
			array(
				new TripleNode(
					new MissingNode(),
					new ResourceListNode(array(new StringResourceNode('VIAF'))),
					new ResourceListNode(array(new StringResourceNode('113230702')))
				)
			),
			array(
				new UnionNode(array(new IntersectionNode(array(new TripleNode(
					new MissingNode(),
					new ResourceListNode(array(new StringResourceNode('VIAF'))),
					new ResourceListNode(array(new StringResourceNode('113230702')))
				)))))
			),
		);
	}

	public function nonSimplifiableProvider() {
		return array(
			array(
				new MissingNode()
			),
			array(
				new TripleNode(
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))))),
					new MissingNode(),
						new ResourceListNode(array(new WikibaseResourceNode('', new StringValue('113230702'))))
				)
			),
			array(
				new TripleNode(
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))))),
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
					new MissingNode()
				)
			),
			array(
				new UnionNode(array(new IntersectionNode(array(new TripleNode(
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))))),
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
					new MissingNode()
				)))))
			),
		);
	}

	/**
	 * @dataProvider simplifiedTripleProvider
	 */
	public function testSimplify(AbstractNode $queryNode, ResourceListNode $responseNodes, Description $description, array $queryResult, ResourceListNode $parsedPredicates, ResourceListNode $parsedObjects, array $entities) {
		$itemIdForQueryLookupMock = $this->getMockBuilder('Wikibase\EntityStore\ItemIdForQueryLookup')
			->disableOriginalConstructor()
			->getMock();
		$itemIdForQueryLookupMock->expects($this->any())
			->method('getItemIdsForQuery')
			->with($this->equalTo($description), $this->equalTo(new QueryOptions(50, 0)))
			->will($this->returnValue($queryResult));

		$entityStoreMock = $this->getMock(
			'Wikibase\EntityStore\InMemory\InMemoryEntityStore',
			array('getItemIdForQueryLookup'),
			array($entities)
		);
		$entityStoreMock->expects($this->any())
			->method('getItemIdForQueryLookup')
			->will($this->returnValue($itemIdForQueryLookupMock));

		$resourceListNodeParserMock = $this->getMockBuilder('PPP\Wikidata\ValueParsers\ResourceListNodeParser')
			->disableOriginalConstructor()
			->getMock();
		$resourceListNodeParserMock->expects($this->any())
			->method('parse')
			->will($this->onConsecutiveCalls(
				$parsedPredicates,
				$parsedObjects,
				$parsedPredicates,
				$parsedObjects,
				$parsedPredicates,
				$parsedObjects
			));

		$simplifier = new MissingSubjectTripleNodeSimplifier(
			new NodeSimplifierFactory(),
			$entityStoreMock,
			$resourceListNodeParserMock
		);

		$this->assertEquals(
			$responseNodes,
			$simplifier->simplify($queryNode)
		);
	}

	public function simplifiedTripleProvider() {
		return array(
			array(
				new TripleNode(
					new MissingNode(),
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
					new ResourceListNode(array(new WikibaseResourceNode('', new StringValue('113230702'))))
				),
				new ResourceListNode(array(
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42')))))
				)),
				new SomeProperty(new EntityIdValue(new PropertyId('P214')), new ValueDescription(new StringValue('113230702'))),
				array(
					new ItemId('Q42')
				),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
				new ResourceListNode(array(new WikibaseResourceNode('', new StringValue('113230702')))),
				array(
					new Property(new PropertyId('P214'), null, 'string')
				)
			),
			array(
				new UnionNode(array(new TripleNode(
					new MissingNode(),
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
					new ResourceListNode(array(
						new WikibaseResourceNode('', new StringValue('113230702')),
						new WikibaseResourceNode('', new StringValue('113230700'))
					))
				))),
				new ResourceListNode(array(
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42')))))
				)),
				new SomeProperty(
					new EntityIdValue(new PropertyId('P214')),
					new Disjunction(array(
						new ValueDescription(new StringValue('113230702')),
						new ValueDescription(new StringValue('113230700'))
					))
				),
				array(
					new ItemId('Q42')
				),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
				new ResourceListNode(array(
					new WikibaseResourceNode('', new StringValue('113230702')),
					new WikibaseResourceNode('', new StringValue('113230700'))
				)),
				array(
					new Property(new PropertyId('P214'), null, 'string')
				)
			),
			array(
				new IntersectionNode(array(new TripleNode(
					new MissingNode(),
					new ResourceListNode(array(
						new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P213'))),
						new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214')))
					)),
					new ResourceListNode(array(new WikibaseResourceNode('', new StringValue('113230702'))))
				))),
				new ResourceListNode(array(
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42')))))
				)),
				new Disjunction(array(
					new SomeProperty(new EntityIdValue(new PropertyId('P213')), new ValueDescription(new StringValue('113230702'))),
					new SomeProperty(new EntityIdValue(new PropertyId('P214')), new ValueDescription(new StringValue('113230702')))
				)),
				array(
					new ItemId('Q42')
				),
				new ResourceListNode(array(
					new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P213'))),
					new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214')))
				)),
				new ResourceListNode(array(new WikibaseResourceNode('', new StringValue('113230702')))),
				array(
					new Property(new PropertyId('P213'), null, 'string'),
					new Property(new PropertyId('P214'), null, 'string')
				)
			),
			array(
				new TripleNode(
					new MissingNode(),
					new ResourceListNode(array(
						new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P213'))),
						new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214')))
					)),
					new ResourceListNode(array(new StringResourceNode('491268')))
				),
				new ResourceListNode(array(
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q456')))))
				)),
				new Disjunction(array(
					new SomeProperty(new EntityIdValue(new PropertyId('P213')), new ValueDescription(new StringValue('491268'))),
					new SomeProperty(new EntityIdValue(new PropertyId('P214')), new ValueDescription(new StringValue('491268')))
				)),
				array(
					new ItemId('Q456')
				),
				new ResourceListNode(array(
					new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P213'))),
					new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214')))
				)),
				new ResourceListNode(array(new WikibaseResourceNode('491268', new StringValue('491268')))),
				array(
					new Property(new PropertyId('P213'), null, 'string'),
					new Property(new PropertyId('P214'), null, 'string')
				)
			),
			array(
				new IntersectionNode(array(
					new UnionNode(array(
						new TripleNode(
							new MissingNode(),
							new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
							new ResourceListNode(array(new StringResourceNode('491268')))
						),
						new TripleNode(
							new MissingNode(),
							new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
							new ResourceListNode(array(new StringResourceNode('491268')))
						),
					)),
					new TripleNode(
						new MissingNode(),
						new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
						new ResourceListNode(array(new StringResourceNode('491268')))
					),
				)),
				new ResourceListNode(array(
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q456')))))
				)),
				new Conjunction(array(
					new Disjunction(array(
						new SomeProperty(new EntityIdValue(new PropertyId('P214')), new ValueDescription(new StringValue('491268'))),
						new SomeProperty(new EntityIdValue(new PropertyId('P214')), new ValueDescription(new StringValue('491268')))
					)),
					new SomeProperty(new EntityIdValue(new PropertyId('P214')), new ValueDescription(new StringValue('491268')))
				)),
				array(
					new ItemId('Q456')
				),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
				new ResourceListNode(array(new WikibaseResourceNode('491268', new StringValue('491268')))),
				array(
					new Property(new PropertyId('P214'), null, 'string')
				)
			),
			array(
				new TripleNode(
					new MissingNode(),
					new UnionNode(array(
						new ResourceListNode(),
						new ResourceListNode(array(new StringResourceNode('VIAF')))
					)),
					new ResourceListNode(array(new StringResourceNode('113230702')))
				),
				new ResourceListNode(array(
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42')))))
				)),
				new SomeProperty(new EntityIdValue(new PropertyId('P214')), new ValueDescription(new StringValue('113230702'))),
				array(
					new ItemId('Q42')
				),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
				new ResourceListNode(array(new WikibaseResourceNode('', new StringValue('113230702')))),
				array(
					new Property(new PropertyId('P214'), null, 'string')
				)
			),
			array(
				new TripleNode(
					new MissingNode(),
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
					new ResourceListNode()
				),
				new ResourceListNode(),
				new AnyValue(),
				array(
					new ItemId('Q42')
				),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
				new ResourceListNode(),
				array(
					new Property(new PropertyId('P214'), null, 'string')
				)
			),
			array(
				new TripleNode(
					new MissingNode(),
					new ResourceListNode(),
					new ResourceListNode(array(new StringResourceNode('491268')))
				),
				new ResourceListNode(),
				new AnyValue(),
				array(
					new ItemId('Q42')
				),
				new ResourceListNode(),
				new ResourceListNode(array(new StringResourceNode('491268'))),
				array()
			),
			array(
				new UnionNode(array()),
				new ResourceListNode(),
				new AnyValue(),
				array(
					new ItemId('Q42')
				),
				new ResourceListNode(),
				new ResourceListNode(),
				array()
			),
			array(
				new UnionNode(array(
					new TripleNode(
						new MissingNode(),
						new ResourceListNode(),
						new ResourceListNode(array(new StringResourceNode('491268')))
					),
				)),
				new ResourceListNode(),
				new AnyValue(),
				array(
					new ItemId('Q42')
				),
				new ResourceListNode(),
				new ResourceListNode(array(new StringResourceNode('491268'))),
				array()
			),
		);
	}
}
