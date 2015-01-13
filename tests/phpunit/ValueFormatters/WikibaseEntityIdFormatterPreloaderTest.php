<?php

namespace PPP\Wikidata;

use DataValues\StringValue;
use PPP\DataModel\BooleanResourceNode;
use PPP\DataModel\ResourceListNode;
use PPP\Wikidata\ValueFormatters\WikibaseEntityIdFormatterPreloader;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\SiteLink;

/**
 * @covers PPP\Wikidata\WikibaseEntityIdFormatterPreloader
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseEntityIdFormatterPreloaderTest extends \PHPUnit_Framework_TestCase {

	public function testPreload() {
		$item = Item::newEmpty();
		$item->getSiteLinkList()->addNewSiteLink('enwiki', 'Foo');

		$entityProviderMock = $this->getMockBuilder('PPP\Wikidata\WikibaseEntityProvider')
			->disableOriginalConstructor()
			->getMock();
		$entityProviderMock->expects($this->once())
			->method('loadEntities')
			->with($this->equalTo(array(new ItemId('Q1'))));
		$entityProviderMock->expects($this->once())
			->method('getItem')
			->with($this->equalTo(new ItemId('Q1')))
			->will($this->returnValue($item));

		$perSiteLinkProviderMock = $this->getMockBuilder('PPP\Wikidata\Wikipedia\MediawikiArticleImageProvider')
			->disableOriginalConstructor()
			->getMock();
		$perSiteLinkProviderMock->expects($this->once())
			->method('loadFromSiteLinks')
			->with($this->equalTo(array(new SiteLink('enwiki', 'Foo'))))
			->will($this->returnValue(null));

		$entityIdFormatterPreloader = new WikibaseEntityIdFormatterPreloader($entityProviderMock, array($perSiteLinkProviderMock), 'en');

		$entityIdFormatterPreloader->preload(new ResourceListNode(array(
			new BooleanResourceNode('1'),
			new WikibaseResourceNode('', new StringValue('a')),
			new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q1')))
		)));
	}
}
