<?php

namespace PPP\Wikidata\SentenceTreeSimplifier;

use DataValues\BooleanValue;
use DataValues\StringValue;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\TripleNode;
use PPP\Wikidata\WikibaseResourceNode;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use WikidataQueryApi\DataModel\AbstractQuery;
use WikidataQueryApi\DataModel\ClaimQuery;

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
	public function testSimplify(TripleNode $queryNode, AbstractNode $responseNode, AbstractQuery $query, array $queryResult) {
		$queryServiceMock = $this->getMockBuilder( 'WikidataQueryApi\Services\SimpleQueryService' )
			->disableOriginalConstructor()
			->getMock();
		$queryServiceMock->expects($this->any())
			->method('doQuery')
			->with($this->equalTo($query))
			->will($this->returnValue($queryResult));

		$simplifier = new MissingSubjectTripleNodeSimplifier($queryServiceMock);
		$this->assertEquals(
			$responseNode,
			$simplifier->simplify($queryNode)
		);
	}

	public function simplifiedTripleProvider() {
		return array(
			array(
				new TripleNode(
					new MissingNode(),
					new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P19'))),
					new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q350')))
				),
				new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q42'))),
				new ClaimQuery(new PropertyId('P19'), new ItemId('Q350')),
				array(
					new ItemId('Q42')
				)
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
					new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P19'))),
					new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q350')))
				),
				new ClaimQuery(new PropertyId('P19'), new ItemId('Q350'))
			),
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
