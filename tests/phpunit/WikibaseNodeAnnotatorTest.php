<?php

namespace PPP\Wikidata;

use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceNode;
use PPP\DataModel\TripleNode;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers PPP\Wikidata\WikibaseNodeAnnotator
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseNodeAnnotatorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider annotatedNodeProvider
	 */
	public function testAnnotateNode(AbstractNode $inputNode, AbstractNode $expectedNode) {
		$itemParserMock = $this->getMock('ValueParsers\ValueParser');
		$itemParserMock->expects($this->any())
			->method('parse')
			->with($this->equalTo('Douglas Adams'))
			->will($this->returnValue(new ItemId('Q42')));

		$propertyParserMock = $this->getMock('ValueParsers\ValueParser');
		$propertyParserMock->expects($this->any())
			->method('parse')
			->with($this->equalTo('Place of birth'))
			->will($this->returnValue(new PropertyId('P569')));

		$propertyTypeProviderMock = $this->getMockBuilder('PPP\Wikidata\WikibasePropertyTypeProvider')
			->disableOriginalConstructor()
			->getMock();
		$propertyTypeProviderMock->expects($this->any())
			->method('getTypeForProperty')
			->with($this->equalTo(new PropertyId( 'P569')))
			->will($this->returnValue('time'));

		$annotator = new WikibaseNodeAnnotator($itemParserMock, $propertyParserMock, $propertyTypeProviderMock);
		$this->assertEquals($expectedNode, $annotator->annotateNode($inputNode));
	}

	public function annotatedNodeProvider() {
		return array(
			array(
				new ResourceNode('Douglas Adams'),
				new ResourceNode('Douglas Adams')
			),
			array(
				new TripleNode(
					new ResourceNode('Douglas Adams'),
					new ResourceNode('Place of birth'),
					new MissingNode()
				),
				new TripleNode(
					new WikibaseResourceNode('Douglas Adams', new EntityIdValue(new ItemId('Q42'))),
					new WikibaseResourceNode('Place of birth', new EntityIdValue(new PropertyId('P569'))),
					new MissingNode()
				)
			),
		);
	}
}
