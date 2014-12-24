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

/**
 * @covers PPP\Wikidata\TreeSimplifier\IntersectionWithFilterNodeSimplifierTest
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class IntersectionWithFilterNodeSimplifierTest extends NodeSimplifierBaseTest {

	public function buildSimplifier() {
		$intersectionNodeSimplifierMock = $this->getMock('PPP\Module\TreeSimplifier\NodeSimplifier');
		$entityProviderMock = $this->getMockBuilder('PPP\Wikidata\WikibaseEntityProvider')
			->disableOriginalConstructor()
			->getMock();
		$resourceListNodeParserMock = $this->getMockBuilder('PPP\Wikidata\ValueParsers\ResourceListNodeParser')
			->disableOriginalConstructor()
			->getMock();

		return new IntersectionWithFilterNodeSimplifier($intersectionNodeSimplifierMock, $entityProviderMock, $resourceListNodeParserMock);
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
	public function testSimplify(IntersectionNode $intersectionNode, AbstractNode $responseNode, IntersectionNode $recursiveCall = null, AbstractNode $recusiveResult = null, ResourceListNode $parsedBaseList, ResourceListNode $parsedPredicates, ResourceListNode $parsedObjects, array $items, array $properties) {
		$intersectionNodeSimplifierMock = $this->getMock('PPP\Module\TreeSimplifier\NodeSimplifier');
		$intersectionNodeSimplifierMock->expects($this->any())
			->method('simplify')
			->with($this->equalTo($recursiveCall))
			->will($this->returnValue($recusiveResult));

		$entityProviderMock = $this->getMockBuilder('PPP\Wikidata\WikibaseEntityProvider')
			->disableOriginalConstructor()
			->getMock();
		$entityProviderMock->expects($this->any())
			->method('getItem')
			->will(call_user_func_array(array($this, 'onConsecutiveCalls'), $items));
		$entityProviderMock->expects($this->any())
			->method('getProperty')
			->will(call_user_func_array(array($this, 'onConsecutiveCalls'), $properties));

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

		$simplifier = new IntersectionWithFilterNodeSimplifier($intersectionNodeSimplifierMock, $entityProviderMock, $resourceListNodeParserMock);

		$this->assertEquals(
			$responseNode,
			$simplifier->simplify($intersectionNode)
		);
	}

	public function simplifiedTripleProvider() {
		$q42 = Item::newEmpty();
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
				array(),
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
				array(),
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
				array(),
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
					Item::newEmpty()
				),
				array(
					Property::newFromType('string')
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
					Item::newEmpty()
				),
				array(
					Property::newFromType('string')
				)
			),
		);
	}
}
