<?php

namespace PPP\Wikidata\TreeSimplifier;

use DataValues\StringValue;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\IntersectionNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\StringResourceNode;
use PPP\DataModel\TripleNode;
use PPP\Wikidata\WikibaseResourceNode;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\EntityStore\InMemory\InMemoryEntityStore;

/**
 * @covers PPP\Wikidata\TreeSimplifier\IntersectionWithFilterNodeSimplifierTest
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class IntersectionWithFilterNodeSimplifierTest extends NodeSimplifierBaseTest {

	public function buildSimplifier() {
		$intersectionNodeSimplifierMock = $this->getMock('PPP\Module\TreeSimplifier\NodeSimplifier');
		$entityStoreMock = $this->getMock('Wikibase\EntityStore\EntityStore');
		$resourceListNodeParserMock = $this->getMockBuilder('PPP\Wikidata\ValueParsers\ResourceListNodeParser')
			->disableOriginalConstructor()
			->getMock();

		return new IntersectionWithFilterNodeSimplifier($intersectionNodeSimplifierMock, $entityStoreMock, $resourceListNodeParserMock);
	}

	public function simplifiableProvider() {
		return array(
			array(
				new IntersectionNode(array())
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
		);
	}

	/**
	 * @dataProvider simplifiedTripleProvider
	 */
	public function testSimplify(IntersectionNode $intersectionNode, AbstractNode $responseNode, IntersectionNode $recursiveCall = null, AbstractNode $recusiveResult = null, ResourceListNode $parsedBaseList, ResourceListNode $parsedPredicates, ResourceListNode $parsedObjects, array $entities) {
		$intersectionNodeSimplifierMock = $this->getMock('PPP\Module\TreeSimplifier\NodeSimplifier');
		$intersectionNodeSimplifierMock->expects($this->any())
			->method('simplify')
			->with($this->equalTo($recursiveCall))
			->will($this->returnValue($recusiveResult));

		$resourceListNodeParserMock = $this->getMockBuilder('PPP\Wikidata\ValueParsers\ResourceListNodeParser')
			->disableOriginalConstructor()
			->getMock();
		$resourceListNodeParserMock->expects($this->any())
			->method('parse')
			->will($this->onConsecutiveCalls(
				$parsedBaseList,
				$parsedPredicates,
				$parsedObjects
			));

		$simplifier = new IntersectionWithFilterNodeSimplifier(
			$intersectionNodeSimplifierMock,
			new InMemoryEntityStore($entities),
			$resourceListNodeParserMock
		);

		$this->assertEquals(
			$responseNode,
			$simplifier->simplify($intersectionNode)
		);
	}

	public function simplifiedTripleProvider() {
		$q42 = new Item(new ItemId('Q42'));
		$q42->getStatements()->addNewStatement(new PropertyValueSnak(new PropertyId('P214'), new StringValue('113230702')));

		return array(
			array(
				new IntersectionNode(array(new ResourceListNode())),
				new IntersectionNode(array(new ResourceListNode())),
				null,
				null,
				new ResourceListNode(),
				new ResourceListNode(),
				new ResourceListNode(),
				array()
			),
			array(
				new IntersectionNode(array(new TripleNode(
					new MissingNode(),
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
					new ResourceListNode(array(new WikibaseResourceNode('', new StringValue('113230702'))))
				))),
				new IntersectionNode(array(new TripleNode(
					new MissingNode(),
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
					new ResourceListNode(array(new WikibaseResourceNode('', new StringValue('113230702'))))
				))),
				null,
				null,
				new ResourceListNode(),
				new ResourceListNode(),
				new ResourceListNode(),
				array()
			),
			array(
				new IntersectionNode(array(
					new MissingNode(),
					new TripleNode(
						new MissingNode(),
						new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
						new ResourceListNode(array(new WikibaseResourceNode('', new StringValue('113230702'))))
					)
				)),
				new IntersectionNode(array(
					new TripleNode(
						new MissingNode(),
						new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
						new ResourceListNode(array(new WikibaseResourceNode('', new StringValue('113230702'))))
					),
					new MissingNode()
				)),
				new IntersectionNode(array(new MissingNode())),
				new MissingNode(),
				new ResourceListNode(),
				new ResourceListNode(),
				new ResourceListNode(),
				array()
			),
			array(
				new IntersectionNode(array(
					new ResourceListNode(array(
						new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))),
						new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q43')))
					)),
					new TripleNode(
						new MissingNode(),
						new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
						new ResourceListNode(array(new WikibaseResourceNode('', new StringValue('113230702'))))
					)
				)),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))))),
				new IntersectionNode(array(new ResourceListNode(array(
					new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))),
					new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q43')))
				)))),
				new ResourceListNode(array(
					new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))),
					new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q43')))
				)),
				new ResourceListNode(array(
					new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))),
					new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q43')))
				)),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
				new ResourceListNode(array(new WikibaseResourceNode('', new StringValue('113230702')))),
				array(
					$q42,
					new Item(new ItemId('Q43')),
					new Property(new PropertyId('P214'), null, 'string')
				)
			),
			array(
				new IntersectionNode(array(
					new ResourceListNode(array(
						new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))),
						new StringResourceNode('foo')
					)),
					new TripleNode(
						new MissingNode(),
						new ResourceListNode(array(new StringResourceNode('VIAF'))),
						new ResourceListNode(array(new StringResourceNode('113230702')))
					)
				)),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))))),
				new IntersectionNode(array(new ResourceListNode(array(
					new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))),
					new StringResourceNode('foo')
				)))),
				new ResourceListNode(array(
					new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))),
					new StringResourceNode('foo')
				)),
				new ResourceListNode(array(
					new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))),
					new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q43')))
				)),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
				new ResourceListNode(array(new WikibaseResourceNode('', new StringValue('113230702')))),
				array(
					$q42,
					new Item(new ItemId('Q43')),
					new Property(new PropertyId('P214'), null, 'string')
				)
			),
		);
	}
}
