<?php

namespace PPP\Wikidata;

use DataValues\StringValue;
use PPP\DataModel\BooleanResourceNode;
use PPP\DataModel\ResourceListNode;
use PPP\Wikidata\ValueFormatters\WikibaseEntityIdFormatterPreloader;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\SiteLink;
use Wikibase\EntityStore\InMemory\InMemoryEntityStore;

/**
 * @covers PPP\Wikidata\ValueFormatters\WikibaseEntityIdFormatterPreloader
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseEntityIdFormatterPreloaderTest extends \PHPUnit_Framework_TestCase {

	public function testPreload() {
		$item = new Item(new ItemId('Q1'));
		$item->getSiteLinkList()->addNewSiteLink('enwiki', 'Foo');

		$perSiteLinkProviderMock = $this->getMockBuilder('PPP\Wikidata\Wikipedia\MediawikiArticleImageProvider')
			->disableOriginalConstructor()
			->getMock();
		$perSiteLinkProviderMock->expects($this->once())
			->method('loadFromSiteLinks')
			->with($this->equalTo(array(new SiteLink('enwiki', 'Foo'))))
			->will($this->returnValue(null));

		$entityIdFormatterPreloader = new WikibaseEntityIdFormatterPreloader(
			new InMemoryEntityStore(array($item, new Property(new PropertyId('P1'), null, 'string'))),
			array($perSiteLinkProviderMock),
			'en'
		);

		$entityIdFormatterPreloader->preload(new ResourceListNode(array(
			new BooleanResourceNode('1'),
			new WikibaseResourceNode('', new StringValue('a')),
			new WikibaseResourceNode('', new EntityIdValue(new ItemId('Q1'))),
			new WikibaseResourceNode('', new EntityIdValue(new PropertyId('P1')))
		)));
	}
}
