<?php

namespace PPP\Wikidata\TreeSimplifier;

use DataValues\StringValue;
use PPP\DataModel\ResourceListNode;
use PPP\Wikidata\WikibaseResourceNode;
use Wikibase\DataModel\Claim\Claim;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyNoValueSnak;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\EntityStore\InMemory\InMemoryEntityStore;

/**
 * @covers PPP\Wikidata\TreeSimplifier\ResourceListForEntityProperty
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class ResourceListForEntityPropertyTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider getForEntityPropertyProvider
	 */
	public function testGetForEntityProperty(EntityDocument $entity, PropertyId $propertyId, ResourceListNode $result) {
		$entityStore = new InMemoryEntityStore(array($entity));
		$resourceListForEntityProperty = new ResourceListForEntityProperty($entityStore);

		$this->assertEquals($result, $resourceListForEntityProperty->getForEntityProperty($entity->getId(), $propertyId));
	}

	/**
	 * @see NodeSimplifierBaseTest::simplifiableProvider
	 */
	public function getForEntityPropertyProvider() {
		return array(
			array(
				new Item(
					new ItemId('Q42'),
					null,
					null,
					new StatementList(new Statement(new Claim(new PropertyValueSnak(new PropertyId('P10'), new StringValue('foo')))))
				),
				new PropertyId('P10'),
				new ResourceListNode(array(
					new WikibaseResourceNode('', new StringValue('foo'), new ItemId('Q42'), new PropertyId('P10'))
				))
			),
			array(
				new Property(
					new PropertyId('P1'),
					null,
					'string',
					new StatementList(new Statement(new Claim(new PropertyValueSnak(new PropertyId('P10'), new StringValue('foo')))))
				),
				new PropertyId('P10'),
				new ResourceListNode(array(
					new WikibaseResourceNode('', new StringValue('foo'), new PropertyId('P1'), new PropertyId('P10'))
				))
			),
			array(
				new Item(
					new ItemId('Q42'),
					null,
					null,
					new StatementList(new Statement(new Claim(new PropertyNoValueSnak(new PropertyId('P10')))))
				),
				new PropertyId('P10'),
				new ResourceListNode(array())
			),
			array(
				new Item(
					new ItemId('Q42')
				),
				new PropertyId('P10'),
				new ResourceListNode(array())
			),
		);
	}
}
