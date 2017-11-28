<?php

namespace PPP\Wikidata\ValueParsers;

use DataValues\StringValue;
use Mediawiki\Api\MediawikiApi;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\StringResourceNode;
use PPP\Wikidata\WikibaseResourceNode;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\EntityStore\Api\ApiEntityStore;

/**
 * @covers PPP\Wikidata\ValueParsers\ResourceListNodeParser
 *
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 *
 * @todo mock instead of requests to the real API?
 */
class ResourceListNodeParserTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider annotatedNodeProvider
	 */
	public function testParse(ResourceListNode $inputNode, $type, ResourceListNode $expectedNode) {
		$valueParserFactory = new WikibaseValueParserFactory(
			'en',
			new ApiEntityStore(new MediawikiApi('http://www.wikidata.org/w/api.php'))
		);
		$resourceListNodeParser = new ResourceListNodeParser($valueParserFactory->newWikibaseValueParser());

		$this->assertEquals($expectedNode, $resourceListNodeParser->parse($inputNode, $type));
	}

	public function annotatedNodeProvider() {
		return array(
			array(
				new ResourceListNode(array(new StringResourceNode('Ramesses III'))),
				'wikibase-item',
				new ResourceListNode(array(new WikibaseResourceNode('Ramesses III', new EntityIdValue(new ItemId('Q1528')))))
			),
			array(
				new ResourceListNode(array(new WikibaseResourceNode('Ramesses III', new EntityIdValue(new ItemId('Q1528'))))),
				'wikibase-item',
				new ResourceListNode(array(new WikibaseResourceNode('Ramesses III', new EntityIdValue(new ItemId('Q1528')))))
			),
			array(
				new ResourceListNode(array(new StringResourceNode('P=NP'))),
				'wikibase-item',
				new ResourceListNode(array(new WikibaseResourceNode('P=NP', new EntityIdValue(new ItemId('Q746242')))))
			),
			array(
				new ResourceListNode(array(new StringResourceNode('Place of birth'))),
				'wikibase-property',
				new ResourceListNode(array(new WikibaseResourceNode('Place of birth', new EntityIdValue(new PropertyId('P19')))))
			),
			array(
				new ResourceListNode(array(new StringResourceNode('foo'))),
				'string',
				new ResourceListNode(array(new WikibaseResourceNode('foo', new StringValue('foo'))))
			),
		);
	}
}
