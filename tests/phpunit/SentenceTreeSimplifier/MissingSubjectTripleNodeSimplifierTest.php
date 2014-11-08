<?php

namespace PPP\Wikidata\SentenceTreeSimplifier;

use DataValues\BooleanValue;
use DataValues\DecimalValue;
use DataValues\GlobeCoordinateValue;
use DataValues\LatLongValue;
use DataValues\QuantityValue;
use DataValues\StringValue;
use DataValues\TimeValue;
use PPP\DataModel\MissingNode;
use PPP\DataModel\TripleNode;
use PPP\Wikidata\WikibaseResourceNode;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use WikidataQueryApi\DataModel\AbstractQuery;
use WikidataQueryApi\DataModel\AroundQuery;
use WikidataQueryApi\DataModel\BetweenQuery;
use WikidataQueryApi\DataModel\ClaimQuery;
use WikidataQueryApi\DataModel\QuantityQuery;
use WikidataQueryApi\DataModel\StringQuery;

/**
 * @covers PPP\Wikidata\SentenceTreeSimplifier\MissingSubjectTripleNodeSimplifier
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class MissingSubjectTripleNodeSimplifierTest extends NodeSimplifierBaseTest {

	/**
	 * @see NodeSimplifierBaseTest::NodeSimplifierBaseTest
	 */
	public function buildDummySimplifier() {
		$queryServiceMock = $this->getMockBuilder( 'WikidataQueryApi\Services\SimpleQueryService' )
			->disableOriginalConstructor()
			->getMock();
		return new MissingSubjectTripleNodeSimplifier($queryServiceMock);
	}

	/**
	 * @see NodeSimplifierBaseTest::simplifiableProvider
	 */
	public function simplifiableProvider() {
		return array(
			array(
				new TripleNode(
					new MissingNode(),
					new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))),
					new WikibaseResourceNode('', new StringValue('113230702'))
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
					new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))),
					new MissingNode(),
					new WikibaseResourceNode('', new StringValue('113230702'))
				)
			),
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
	 * @dataProvider simplifiedTripleProvider
	 */
	public function testSimplify(TripleNode $queryNode, array $responseNodes, AbstractQuery $query, array $queryResult) {
		$queryServiceMock = $this->getMockBuilder( 'WikidataQueryApi\Services\SimpleQueryService' )
			->disableOriginalConstructor()
			->getMock();
		$queryServiceMock->expects($this->any())
			->method('doQuery')
			->with($this->equalTo($query))
			->will($this->returnValue($queryResult));

		$simplifier = new MissingSubjectTripleNodeSimplifier($queryServiceMock);
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
					new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P625'))),
					new WikibaseResourceNode('', new GlobeCoordinateValue(new LatLongValue(45.75972, 4.8422), 0.0002777))
				),
				array(
					new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q456'))),
				),
				new AroundQuery(
					new PropertyId('P625'),
					new LatLongValue(45.75972, 4.8422),
					0.027769999999999996
				),
				array(
					new ItemId('Q456')
				)
			),
			array(
				new TripleNode(
					new MissingNode(),
					new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P1082'))),
					new WikibaseResourceNode('', new QuantityValue(new DecimalValue('+491268'), '1', new DecimalValue('+491268'), new DecimalValue('+491267')))
				),
				array(
					new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q456')))
				),
				new QuantityQuery(new PropertyId('P1082'), new DecimalValue('+491268')),
				array(
					new ItemId('Q456')
				)
			),
			array(
				new TripleNode(
					new MissingNode(),
					new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P214'))),
					new WikibaseResourceNode('', new StringValue('113230702'))
				),
				array(
					new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42')))
				),
				new StringQuery(new PropertyId('P214'), new StringValue('113230702')),
				array(
					new ItemId('Q42')
				)
			),
			array(
				new TripleNode(
					new MissingNode(),
					new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P569'))),
					new WikibaseResourceNode('', new TimeValue('+00000001952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, ''))
				),
				array(
					new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42')))
				),
				new BetweenQuery(
					new PropertyId('P569'),
					new TimeValue('+00000001952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, ''),
					new TimeValue('+00000001952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, '')
				),
				array(
					new ItemId('Q42')
				)
			),
			array(
				new TripleNode(
					new MissingNode(),
					new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P19'))),
					new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q350')))
				),
				array(
					new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42')))
				),
				new ClaimQuery(new PropertyId('P19'), new ItemId('Q350')),
				array(
					new ItemId('Q42')
				)
			),
			array(
				new TripleNode(
					new MissingNode(),
					new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P19'))),
					new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q350')))
				),
				array(),
				new ClaimQuery(new PropertyId('P19'), new ItemId('Q350')),
				array()
			),
		);
	}


	/**
	 * @dataProvider notSimplifiedTripleProvider
	 */
	public function testSimplifyWithException(TripleNode $queryNode, AbstractQuery $query = null, array $queryResult = array()) {
		$queryServiceMock = $this->getMockBuilder( 'WikidataQueryApi\Services\SimpleQueryService' )
			->disableOriginalConstructor()
			->getMock();
		$queryServiceMock->expects($this->any())
			->method('doQuery')
			->with($this->equalTo($query))
			->will($this->returnValue($queryResult));

		$simplifier = new MissingSubjectTripleNodeSimplifier($queryServiceMock);

		$this->setExpectedException('PPP\Wikidata\SentenceTreeSimplifier\SimplifierException');
		$simplifier->simplify($queryNode);
	}

	public function notSimplifiedTripleProvider() {
		return array(
			array(
				new TripleNode(
					new MissingNode(),
					new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P1'))),
					new WikibaseResourceNode('', new BooleanValue(true))
				)
			),
		);
	}
}
