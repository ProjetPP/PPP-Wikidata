<?php

namespace PPP\Wikidata;

use DataValues\StringValue;
use PPP\DataModel\BooleanResourceNode;
use PPP\DataModel\ResourceListNode;
use PPP\Wikidata\ValueFormatters\WikibaseEntityIdFormatterPreloader;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers PPP\Wikidata\WikibaseEntityIdFormatterPreloader
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseEntityIdFormatterPreloaderTest extends \PHPUnit_Framework_TestCase {

	public function testPreload() {
		$entityProviderMock = $this->getMockBuilder('PPP\Wikidata\WikibaseEntityProvider')
			->disableOriginalConstructor()
			->getMock();
		$entityProviderMock->expects($this->once())
			->method('loadEntities')
			->with($this->equalTo(array(new ItemId('Q1'), new ItemId('Q2'))));

		$entityIdFormatterPreloader = new WikibaseEntityIdFormatterPreloader($entityProviderMock);

		$entityIdFormatterPreloader->preload(new ResourceListNode(array(
			new BooleanResourceNode('1'),
			new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q1'))),
			new WikibaseResourceNode('', new StringValue('a')),
			new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q2')))
		)));
	}
}
