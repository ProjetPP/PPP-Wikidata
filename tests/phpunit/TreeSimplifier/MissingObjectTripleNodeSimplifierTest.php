<?php

namespace PPP\Wikidata\TreeSimplifier;

use DataValues\StringValue;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\StringResourceNode;
use PPP\DataModel\TripleNode;
use PPP\Wikidata\WikibaseResourceNode;
use Wikibase\DataModel\Claim\Claim;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertySomeValueSnak;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\EntityStore\InMemory\InMemoryEntityStore;

/**
 * @covers PPP\Wikidata\TreeSimplifier\MissingObjectTripleNodeSimplifier
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class MissingObjectTripleNodeSimplifierTest extends NodeSimplifierBaseTest {

	public function buildSimplifier() {
		$resourceListNodeParserMock = $this->getMockBuilder('PPP\Wikidata\ValueParsers\ResourceListNodeParser')
			->disableOriginalConstructor()
			->getMock();
		$entityStoreMock = $this->getMock('Wikibase\EntityStore\EntityStore');
		return new MissingObjectTripleNodeSimplifier($resourceListNodeParserMock, $entityStoreMock);
	}

	/**
	 * @see NodeSimplifierBaseTest::simplifiableProvider
	 */
	public function simplifiableProvider() {
		return array(
			array(
				new TripleNode(
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))))),
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
					new MissingNode()
				)
			),
			array(
				new TripleNode(
					new ResourceListNode(array(new StringResourceNode('Douglas Adams'))),
					new ResourceListNode(array(new StringResourceNode('VIAF'))),
					new MissingNode()
				)
			),
		);
	}

	/**
	 * @see NodeSimplifierBaseTest::nonSimplifiableProvider
	 */
	public function nonSimplifiableProvider() {
		return array(
			array(
				new MissingNode()
			),
			array(
				new TripleNode(
					new MissingNode(),
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
					new ResourceListNode(array(new WikibaseResourceNode('', new StringValue('113230702'))))
				)
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
	 * @dataProvider simplificationProvider
	 */
	public function testSimplification(TripleNode $queryNode, AbstractNode $responseNodes, Item $item, PropertyId $propertyId) {
		$resourceListNodeParserMock = $this->getMockBuilder('PPP\Wikidata\ValueParsers\ResourceListNodeParser')
			->disableOriginalConstructor()
			->getMock();
		$resourceListNodeParserMock->expects($this->any())
			->method('parse')
			->will($this->onConsecutiveCalls(
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue($item->getId())))),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue($propertyId))))
			));

		$simplifier = new MissingObjectTripleNodeSimplifier(
			$resourceListNodeParserMock,
			new InMemoryEntityStore(array($item))
		);
		$this->assertEquals(
			$responseNodes,
			$simplifier->simplify($queryNode)
		);
	}

	public function simplificationProvider() {
		$list = array();

		//Value
		$douglasAdamItem = new Item();
		$douglasAdamItem->setId(new ItemId('Q42'));
		$birthPlaceStatement = new Statement(new Claim(
			new PropertyValueSnak(new PropertyId('P214'), new StringValue('113230702'))
		));
		$birthPlaceStatement->setGuid('42');
		$douglasAdamItem->getStatements()->addStatement($birthPlaceStatement);
		$list[] = array(
			new TripleNode(
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))))),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
				new MissingNode()
			),
			new ResourceListNode(array(
				new ResourceListNode(array(
					new WikibaseResourceNode(
						'',
						new StringValue('113230702'),
						new ItemId('Q42'),
						new PropertyId('P214')
					)
				))
			)),
			$douglasAdamItem,
			new PropertyId('P214')
		);

		//SomeValue
		$douglasAdamItem = new Item();
		$douglasAdamItem->setId(new ItemId('Q42'));
		$birthPlaceStatement = new Statement(new Claim(new PropertySomeValueSnak(new PropertyId('P19'))));
		$birthPlaceStatement->setGuid('42');
		$douglasAdamItem->getStatements()->addStatement($birthPlaceStatement);
		$list[] = array(
			new TripleNode(
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))))),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P19'))))),
				new MissingNode()
			),
			new ResourceListNode(array()),
			$douglasAdamItem,
			new PropertyId('P19')
		);

		//No result
		$douglasAdamItem = new Item();
		$douglasAdamItem->setId(new ItemId('Q42'));
		$list[] = array(
			new TripleNode(
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))))),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P19'))))),
				new MissingNode()
			),
			new ResourceListNode(array()),
			$douglasAdamItem,
			new PropertyId('P19')
		);

		//Parsing
		$douglasAdamItem = new Item();
		$douglasAdamItem->setId(new ItemId('Q42'));
		$list[] = array(
			new TripleNode(
				new ResourceListNode(array(new StringResourceNode('Douglas Adams'))),
				new ResourceListNode(array(new StringResourceNode('VIAF'))),
				new MissingNode()
			),
			new ResourceListNode(array()),
			$douglasAdamItem,
			new PropertyId('P214')
		);

		return $list;
	}
}
