<?php

namespace PPP\Wikidata;

use DataValues\UnknownValue;
use Mediawiki\Api\MediawikiApi;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceNode;
use PPP\DataModel\TripleNode;
use PPP\Wikidata\ValueParsers\WikibaseValueParserFactory;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers PPP\Wikidata\WikibaseNodeAnnotator
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 *
 * @todo mock instead of requests to the real API?
 */
class WikibaseNodeAnnotatorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider annotatedNodeProvider
	 */
	public function testAnnotateNode(AbstractNode $inputNode, AbstractNode $expectedNode) {
		$valueParserFactory = new WikibaseValueParserFactory('en', new MediawikiApi('http://www.wikidata.org/w/api.php'));

		$propertyTypeProviderMock = $this->getMockBuilder('PPP\Wikidata\WikibasePropertyTypeProvider')
			->disableOriginalConstructor()
			->getMock();
		$propertyTypeProviderMock->expects($this->any())
			->method('getTypeForProperty')
			->with($this->equalTo(new PropertyId( 'P19')))
			->will($this->returnValue('time'));

		$annotator = new WikibaseNodeAnnotator($valueParserFactory->newWikibaseValueParser(), $propertyTypeProviderMock);
		$this->assertEquals($expectedNode, $annotator->annotateNode($inputNode));
	}

	public function annotatedNodeProvider() {
		return array(
			array(
				new ResourceNode('Douglas Adams'),
				new WikibaseResourceNode('Douglas Adams', new UnknownValue('Douglas Adams'))
			),
			array(
				new TripleNode(
					new ResourceNode('Douglas Adams'),
					new ResourceNode('Place of birth'),
					new MissingNode()
				),
				new TripleNode(
					new WikibaseResourceNode('Douglas Adams', new EntityIdValue(new ItemId('Q42'))),
					new WikibaseResourceNode('Place of birth', new EntityIdValue(new PropertyId('P19'))),
					new MissingNode()
				)
			),
		);
	}
}