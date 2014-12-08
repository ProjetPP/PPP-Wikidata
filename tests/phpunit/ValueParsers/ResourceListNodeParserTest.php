<?php

namespace PPP\Wikidata\ValueParsers;

use DataValues\StringValue;
use Doctrine\Common\Cache\ArrayCache;
use Mediawiki\Api\MediawikiApi;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\StringResourceNode;
use PPP\Wikidata\WikibaseResourceNode;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers PPP\Wikidata\ValueParsers\ResourceListNodeParser
 *
 * @licence GPLv2+
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
			new MediawikiApi('http://www.wikidata.org/w/api.php'),
			new ArrayCache()
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
