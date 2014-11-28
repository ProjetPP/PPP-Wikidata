<?php

namespace PPP\Wikidata\TreeSimplifier;

use DataValues\StringValue;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\TripleNode;
use PPP\Module\TreeSimplifier\NodeSimplifierFactory;
use PPP\Wikidata\WikibaseResourceNode;
use Wikibase\DataModel\Claim\Claim;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertySomeValueSnak;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;

/**
 * @covers PPP\Wikidata\SentenceTreeSimplifier\MissingObjectTripleNodeSimplifier
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class MissingObjectTripleNodeSimplifierTest extends NodeSimplifierBaseTest {

	public function buildSimplifier() {
		$entityProvider = $this->getMockBuilder( 'PPP\Wikidata\WikibaseEntityProvider' )
			->disableOriginalConstructor()
			->getMock();
		return new MissingObjectTripleNodeSimplifier(new NodeSimplifierFactory(), $entityProvider);
	}

	/**
	 * @see NodeSimplifierBaseTest::simplifiableProvider
	 */
	public function simplifiableProvider() {
		return array(
			array(
				new TripleNode(
					new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))),
					new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))),
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
					new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))),
					new WikibaseResourceNode('', new StringValue('113230702'))
				)
			),
			array(
				new TripleNode(
					new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))),
					new MissingNode(),
					new WikibaseResourceNode('', new StringValue('113230702'))
				)
			),
		);
	}

	/**
	 * @dataProvider simplificationProvider
	 */
	public function testSimplification(TripleNode $queryNode, AbstractNode $responseNodes, Item $item) {
		$entityProvider = $this->getMockBuilder('PPP\Wikidata\WikibaseEntityProvider')
			->disableOriginalConstructor()
			->getMock();
		$entityProvider->expects($this->any())
			->method('getItem')
			->with($this->equalTo(new ItemId('Q42')))
			->will($this->returnValue($item));

		$simplifier = new MissingObjectTripleNodeSimplifier(new NodeSimplifierFactory(), $entityProvider);
		$this->assertEquals(
			$responseNodes,
			$simplifier->simplify($queryNode)
		);
	}

	public function simplificationProvider() {
		$list = array();

		//Value
		$douglasAdamItem = Item::newEmpty();
		$douglasAdamItem->setId(new ItemId('Q42'));
		$birthPlaceStatement = new Statement(new Claim(
			new PropertyValueSnak(new PropertyId('P214'), new StringValue('113230702'))
		));
		$birthPlaceStatement->setGuid('42');
		$douglasAdamItem->getStatements()->addStatement($birthPlaceStatement);
		$list[] = array(
			new TripleNode(
				new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))),
				new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))),
				new MissingNode()
			),
			new ResourceListNode(array(
				new WikibaseResourceNode('', new StringValue('113230702'))
			)),
			$douglasAdamItem
		);

		//SomeValue
		$douglasAdamItem = Item::newEmpty();
		$douglasAdamItem->setId(new ItemId('Q42'));
		$birthPlaceStatement = new Statement(new Claim(new PropertySomeValueSnak(new PropertyId('P19'))));
		$birthPlaceStatement->setGuid('42');
		$douglasAdamItem->getStatements()->addStatement($birthPlaceStatement);
		$list[] = array(
			new TripleNode(
				new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))),
				new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P19'))),
				new MissingNode()
			),
			new ResourceListNode(array()),
			$douglasAdamItem
		);

		$douglasAdamItem = Item::newEmpty();
		$douglasAdamItem->setId(new ItemId('Q42'));
		$list[] = array(
			new TripleNode(
				new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))),
				new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P19'))),
				new MissingNode()
			),
			new ResourceListNode(array()),
			$douglasAdamItem
		);

		return $list;
	}
}
