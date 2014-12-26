<?php

namespace PPP\Wikidata\TreeSimplifier;

use DataValues\BooleanValue;
use DataValues\DecimalValue;
use DataValues\GlobeCoordinateValue;
use DataValues\LatLongValue;
use DataValues\QuantityValue;
use DataValues\StringValue;
use DataValues\TimeValue;
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
use WikidataQueryApi\Query\AbstractQuery;
use WikidataQueryApi\Query\AndQuery;
use WikidataQueryApi\Query\AroundQuery;
use WikidataQueryApi\Query\BetweenQuery;
use WikidataQueryApi\Query\ClaimQuery;
use WikidataQueryApi\Query\OrQuery;
use WikidataQueryApi\Query\QuantityQuery;
use WikidataQueryApi\Query\StringQuery;

/**
 * @covers PPP\Wikidata\TreeSimplifier\MissingSubjectTripleNodeSimplifier
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class MissingSubjectTripleNodeSimplifierTest extends NodeSimplifierBaseTest {

	public function buildSimplifier() {
		$nodeSimplifierFactoryMock = $this->getMockBuilder('PPP\Module\TreeSimplifier\NodeSimplifierFactory')
			->disableOriginalConstructor()
			->getMock();
		$queryServiceMock = $this->getMockBuilder('WikidataQueryApi\Services\SimpleQueryService')
			->disableOriginalConstructor()
			->getMock();
		$entityProviderMock = $this->getMockBuilder('PPP\Wikidata\WikibaseEntityProvider')
			->disableOriginalConstructor()
			->getMock();
		$resourceListNodeParserMock = $this->getMockBuilder('PPP\Wikidata\ValueParsers\ResourceListNodeParser')
			->disableOriginalConstructor()
			->getMock();

		return new MissingSubjectTripleNodeSimplifier($nodeSimplifierFactoryMock, $queryServiceMock, $entityProviderMock, $resourceListNodeParserMock);
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
	public function testSimplify(AbstractNode $queryNode, ResourceListNode $responseNodes, AbstractQuery $query, array $queryResult, ResourceListNode $parsedPredicates, ResourceListNode $parsedObjects, array $properties) {
		$queryServiceMock = $this->getMockBuilder('WikidataQueryApi\Services\SimpleQueryService')
			->disableOriginalConstructor()
			->getMock();
		$queryServiceMock->expects($this->any())
			->method('doQuery')
			->with($this->equalTo($query))
			->will($this->returnValue($queryResult));

		$entityProviderMock = $this->getMockBuilder('PPP\Wikidata\WikibaseEntityProvider')
			->disableOriginalConstructor()
			->getMock();
		$entityProviderMock->expects($this->any())
			->method('loadEntities')
			->with($this->equalTo($queryResult));
		$entityProviderMock->expects($this->any())
			->method('getProperty')
			->will(call_user_func_array(array($this, 'onConsecutiveCalls'), $properties));

		$resourceListNodeParserMock = $this->getMockBuilder('PPP\Wikidata\ValueParsers\ResourceListNodeParser')
			->disableOriginalConstructor()
			->getMock();
		$resourceListNodeParserMock->expects($this->any())
			->method('parse')
			->will($this->onConsecutiveCalls(
				$parsedPredicates,
				$parsedObjects
			));

		$simplifier = new MissingSubjectTripleNodeSimplifier(new NodeSimplifierFactory(), $queryServiceMock, $entityProviderMock, $resourceListNodeParserMock);

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
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P625'))))),
					new ResourceListNode(array(new WikibaseResourceNode('', new GlobeCoordinateValue(new LatLongValue(45.75972, 4.8422), 0.0002777))))
				),
				new ResourceListNode(array(
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q456'))))),
				)),
				new OrQuery(array(new AroundQuery(
					new PropertyId('P625'),
					new LatLongValue(45.75972, 4.8422),
					0.027769999999999996
				))),
				array(
					new ItemId('Q456')
				),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P625'))))),
				new ResourceListNode(array(new WikibaseResourceNode('', new GlobeCoordinateValue(new LatLongValue(45.75972, 4.8422), 0.0002777)))),
				array(
					Property::newFromType('globecoordinate')
				)
			),
			array(
				new TripleNode(
					new MissingNode(),
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P1082'))))),
					new ResourceListNode(array(new WikibaseResourceNode('', new QuantityValue(new DecimalValue('+491268'), '1', new DecimalValue('+491268'), new DecimalValue('+491267')))))
				),
				new ResourceListNode(array(
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q456')))))
				)),
				new OrQuery(array(new QuantityQuery(new PropertyId('P1082'), new DecimalValue('+491268')))),
				array(
					new ItemId('Q456')
				),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P1082'))))),
				new ResourceListNode(array(new WikibaseResourceNode('', new QuantityValue(new DecimalValue('+491268'), '1', new DecimalValue('+491268'), new DecimalValue('+491267'))))),
				array(
					Property::newFromType('quantity')
				)
			),
			array(
				new TripleNode(
					new MissingNode(),
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
					new ResourceListNode(array(new WikibaseResourceNode('', new StringValue('113230702'))))
				),
				new ResourceListNode(array(
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42')))))
				)),
				new OrQuery(array(new StringQuery(new PropertyId('P214'), new StringValue('113230702')))),
				array(
					new ItemId('Q42')
				),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
				new ResourceListNode(array(new WikibaseResourceNode('', new StringValue('113230702')))),
				array(
					Property::newFromType('string')
				)
			),
			array(
				new TripleNode(
					new MissingNode(),
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P569'))))),
					new ResourceListNode(array(new WikibaseResourceNode('', new TimeValue('+00000001952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, ''))))
				),
				new ResourceListNode(array(
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42')))))
				)),
				new OrQuery(array(new BetweenQuery(
					new PropertyId('P569'),
					new TimeValue('+00000001952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, ''),
					new TimeValue('+00000001952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, '')
				))),
				array(
					new ItemId('Q42')
				),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P569'))))),
				new ResourceListNode(array(new WikibaseResourceNode('', new TimeValue('+00000001952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, '')))),
				array(
					Property::newFromType('time')
				)
			),
			array(
				new TripleNode(
					new MissingNode(),
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P19'))))),
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q350')))))
				),
				new ResourceListNode(array(
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42')))))
				)),
				new OrQuery(array(new ClaimQuery(new PropertyId('P19'), new ItemId('Q350')))),
				array(
					new ItemId('Q42')
				),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P19'))))),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q350'))))),
				array(
					Property::newFromType('wikibase-entityid')
				)
			),
			array(
				new TripleNode(
					new MissingNode(),
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P19'))))),
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q350')))))
				),
				new ResourceListNode(array()),
				new OrQuery(array(new ClaimQuery(new PropertyId('P19'), new ItemId('Q350')))),
				array(),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P19'))))),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q350'))))),
				array(
					Property::newFromType('wikibase-entityid')
				)
			),
			array(
				new TripleNode(
					new MissingNode(),
					new ResourceListNode(array(new StringResourceNode('VIAF'))),
					new ResourceListNode(array(new StringResourceNode('113230702')))
				),
				new ResourceListNode(array(
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42')))))
				)),
				new OrQuery(array(new StringQuery(new PropertyId('P214'), new StringValue('113230702')))),
				array(
					new ItemId('Q42')
				),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
				new ResourceListNode(array(new WikibaseResourceNode('', new StringValue('113230702')))),
				array(
					Property::newFromType('string')
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
				new OrQuery(array(
					new StringQuery(new PropertyId('P213'), new StringValue('491268')),
					new StringQuery(new PropertyId('P214'), new StringValue('491268'))
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
					Property::newFromType('string'),
					Property::newFromType('string')
				)
			),
			array(
				new IntersectionNode(array(
					new UnionNode(array(
						new TripleNode(
							new MissingNode(),
							new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
							new ResourceListNode(array(new StringResourceNode('491268')))
						)
					))
				)),
				new ResourceListNode(array(
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q456')))))
				)),
				new AndQuery(array(new OrQuery(array(
					new OrQuery(array(new StringQuery(new PropertyId('P214'), new StringValue('491268'))))
				)))),
				array(
					new ItemId('Q456')
				),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
				new ResourceListNode(array(new WikibaseResourceNode('491268', new StringValue('491268')))),
				array(
					Property::newFromType('string')
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
				new OrQuery(array(new StringQuery(new PropertyId('P214'), new StringValue('113230702')))),
				array(
					new ItemId('Q42')
				),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))))),
				new ResourceListNode(array(new WikibaseResourceNode('', new StringValue('113230702')))),
				array(
					Property::newFromType('string')
				)
			),
		);
	}


	/**
	 * @dataProvider notSimplifiedTripleProvider
	 */
	public function testSimplifyWithException(AbstractNode $queryNode, AbstractQuery $query = null, array $queryResult, ResourceListNode $parsedPredicates, ResourceListNode $parsedObjects, array $properties) {
		$queryServiceMock = $this->getMockBuilder( 'WikidataQueryApi\Services\SimpleQueryService' )
			->disableOriginalConstructor()
			->getMock();
		$queryServiceMock->expects($this->any())
			->method('doQuery')
			->with($this->equalTo($query))
			->will($this->returnValue($queryResult));

		$entityProviderMock = $this->getMockBuilder('PPP\Wikidata\WikibaseEntityProvider')
			->disableOriginalConstructor()
			->getMock();
		$entityProviderMock->expects($this->any())
			->method('getProperty')
			->will(call_user_func_array(array($this, 'onConsecutiveCalls'), $properties));


		$resourceListNodeParserMock = $this->getMockBuilder('PPP\Wikidata\ValueParsers\ResourceListNodeParser')
			->disableOriginalConstructor()
			->getMock();
		$resourceListNodeParserMock->expects($this->any())
			->method('parse')
			->will($this->onConsecutiveCalls(
				$parsedPredicates,
				$parsedObjects
			));

		$simplifier = new MissingSubjectTripleNodeSimplifier(new NodeSimplifierFactory(), $queryServiceMock, $entityProviderMock, $resourceListNodeParserMock);

		$this->setExpectedException('PPP\Module\TreeSimplifier\NodeSimplifierException');
		$simplifier->simplify($queryNode);
	}

	public function notSimplifiedTripleProvider() {
		return array(
			array(
				new TripleNode(
					new MissingNode(),
					new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P1'))))),
					new ResourceListNode(array(new WikibaseResourceNode('', new BooleanValue(true))))
				),
				null,
				array(),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P1'))))),
				new ResourceListNode(array(new WikibaseResourceNode('', new BooleanValue(true)))),
				array(
					Property::newFromType('boolean')
				)
			),
		);
	}
}
