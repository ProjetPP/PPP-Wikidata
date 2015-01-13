<?php

namespace PPP\Wikidata;

use Doctrine\Common\Cache\ArrayCache;
use Mediawiki\DataModel\Revision;
use Mediawiki\DataModel\Revisions;
use PPP\Wikidata\Cache\WikibaseEntityCache;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\ItemContent;
use Wikibase\DataModel\PropertyContent;

/**
 * @covers PPP\Wikidata\WikibaseEntityProvider
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseEntityProviderTest extends \PHPUnit_Framework_TestCase {

	public function testGetEntityDocument() {
		$item = Item::newEmpty();
		$item->setId(new ItemId('Q42'));

		$revisionGetterMock = $this->getMockBuilder('Wikibase\Api\Service\RevisionsGetter')
			->disableOriginalConstructor()
			->getMock();
		$revisionGetterMock->expects($this->once())
			->method('getRevisions')
			->with($this->equalTo(array(new ItemId('Q42'))))
			->will($this->returnValue(new Revisions(array(new Revision(new ItemContent($item))))));

		$provider = new WikibaseEntityProvider($revisionGetterMock, new WikibaseEntityCache(new ArrayCache()));

		$this->assertEquals($item, $provider->getEntityDocument(new ItemId('Q42')));
	}

	public function testGetEntityDocumentWithException() {
		$revisionGetterMock = $this->getMockBuilder('Wikibase\Api\Service\RevisionsGetter')
			->disableOriginalConstructor()
			->getMock();
		$revisionGetterMock->expects($this->once())
			->method('getRevisions')
			->with($this->equalTo(array(new ItemId('Q42424242'))))
			->will($this->returnValue(new Revisions(array())));

		$provider = new WikibaseEntityProvider($revisionGetterMock, new WikibaseEntityCache(new ArrayCache()));

		$this->setExpectedException('\OutOfBoundsException');
		$provider->getEntityDocument(new ItemId('Q42424242'));
	}

	public function testGetEntityDocumentWithCache() {
		$item = Item::newEmpty();
		$item->setId(new ItemId('Q42'));

		$revisionGetterMock = $this->getMockBuilder('Wikibase\Api\Service\RevisionsGetter')
			->disableOriginalConstructor()
			->getMock();
		$revisionGetterMock->expects($this->once())
			->method('getRevisions')
			->with($this->equalTo(array(new ItemId('Q42'))))
			->will($this->returnValue(new Revisions(array(new Revision(new ItemContent($item))))));

		$provider = new WikibaseEntityProvider($revisionGetterMock, new WikibaseEntityCache(new ArrayCache()));

		$provider->getItem(new ItemId('Q42'));
		$this->assertEquals($item, $provider->getEntityDocument(new ItemId('Q42')));
	}

	public function testGetEntityDocumentWithLoad() {
		$item42 = Item::newEmpty();
		$item42->setId(new ItemId('Q42'));
		$item43 = Item::newEmpty();
		$item43->setId(new ItemId('Q43'));

		$revisionGetterMock = $this->getMockBuilder('Wikibase\Api\Service\RevisionsGetter')
			->disableOriginalConstructor()
			->getMock();
		$revisionGetterMock->expects($this->once())
			->method('getRevisions')
			->with($this->equalTo(array(new ItemId('Q42'), new ItemId('Q43'))))
			->will($this->returnValue(new Revisions(array(
				new Revision(new ItemContent($item42), 42, 42),
				new Revision(new ItemContent($item43), 43, 43)
			))));

		$provider = new WikibaseEntityProvider($revisionGetterMock, new WikibaseEntityCache(new ArrayCache()));

		$provider->loadEntities(array(new ItemId('Q42'), new ItemId('Q43')));
		$provider->getItem(new ItemId('Q42'));
		$this->assertEquals($item42, $provider->getEntityDocument(new ItemId('Q42')));
	}

	public function testGetItem() {
		$item = Item::newEmpty();
		$item->setId(new ItemId('Q42'));

		$revisionGetterMock = $this->getMockBuilder('Wikibase\Api\Service\RevisionsGetter')
			->disableOriginalConstructor()
			->getMock();
		$revisionGetterMock->expects($this->once())
			->method('getRevisions')
			->with($this->equalTo(array(new ItemId('Q42'))))
			->will($this->returnValue(new Revisions(array(new Revision(new ItemContent($item))))));

		$provider = new WikibaseEntityProvider($revisionGetterMock, new WikibaseEntityCache(new ArrayCache()));

		$this->assertEquals($item, $provider->getItem(new ItemId('Q42')));
	}

	public function testGetProperty() {
		$property = Property::newfromType('string');
		$property->setId(new PropertyId('P42'));

		$revisionGetterMock = $this->getMockBuilder('Wikibase\Api\Service\RevisionsGetter')
			->disableOriginalConstructor()
			->getMock();
		$revisionGetterMock->expects($this->once())
			->method('getRevisions')
			->with($this->equalTo(array(new PropertyId('P42'))))
			->will($this->returnValue(new Revisions(array(new Revision(new PropertyContent($property))))));

		$provider = new WikibaseEntityProvider($revisionGetterMock, new WikibaseEntityCache(new ArrayCache()));

		$this->assertEquals($property, $provider->getProperty(new PropertyId('P42')));
	}
}
