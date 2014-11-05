<?php

namespace PPP\Wikidata;

use Doctrine\Common\Cache\ArrayCache;
use Mediawiki\DataModel\Revision;
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

	public function testGetItem() {
		$item = Item::newEmpty();
		$item->setId(new ItemId('Q42'));

		$revisionGetterMock = $this->getMockBuilder( 'Wikibase\Api\Service\RevisionGetter' )
			->disableOriginalConstructor()
			->getMock();
		$revisionGetterMock->expects($this->once())
			->method('getFromId')
			->with($this->equalTo(new ItemId('Q42')))
			->will($this->returnValue(new Revision(new ItemContent($item))));

		$provider = new WikibaseEntityProvider($revisionGetterMock, new WikibaseEntityCache(new ArrayCache()));

		$this->assertEquals($item, $provider->getItem(new ItemId('Q42')));
	}

	public function testGetItemWithException() {
		$revisionGetterMock = $this->getMockBuilder( 'Wikibase\Api\Service\RevisionGetter' )
			->disableOriginalConstructor()
			->getMock();
		$revisionGetterMock->expects($this->once())
			->method('getFromId')
			->with($this->equalTo(new ItemId('Q42424242')))
			->will($this->returnValue(false));

		$provider = new WikibaseEntityProvider($revisionGetterMock, new WikibaseEntityCache(new ArrayCache()));

		$this->setExpectedException('\OutOfRangeException');
		$provider->getItem(new ItemId('Q42424242'));
	}

	public function testGetItemWithCache() {
		$item = Item::newEmpty();
		$item->setId(new ItemId('Q42'));

		$revisionGetterMock = $this->getMockBuilder( 'Wikibase\Api\Service\RevisionGetter' )
			->disableOriginalConstructor()
			->getMock();
		$revisionGetterMock->expects($this->once())
			->method('getFromId')
			->with($this->equalTo(new ItemId('Q42')))
			->will($this->returnValue(new Revision(new ItemContent($item))));

		$provider = new WikibaseEntityProvider($revisionGetterMock, new WikibaseEntityCache(new ArrayCache()));

		$provider->getItem(new ItemId('Q42'));
		$this->assertEquals($item, $provider->getItem(new ItemId('Q42')));
	}

	public function testGetProperty() {
		$property = Property::newfromType('string');
		$property->setId(new PropertyId('P42'));

		$revisionGetterMock = $this->getMockBuilder( 'Wikibase\Api\Service\RevisionGetter' )
			->disableOriginalConstructor()
			->getMock();
		$revisionGetterMock->expects($this->once())
			->method('getFromId')
			->with($this->equalTo(new PropertyId('P42')))
			->will($this->returnValue(new Revision(new PropertyContent($property))));

		$provider = new WikibaseEntityProvider($revisionGetterMock, new WikibaseEntityCache(new ArrayCache()));

		$this->assertEquals($property, $provider->getProperty(new PropertyId('P42')));
	}

	public function testGetPropertWithException() {
		$revisionGetterMock = $this->getMockBuilder( 'Wikibase\Api\Service\RevisionGetter' )
			->disableOriginalConstructor()
			->getMock();
		$revisionGetterMock->expects($this->once())
			->method('getFromId')
			->with($this->equalTo(new PropertyId('P42424242')))
			->will($this->returnValue(false));

		$provider = new WikibaseEntityProvider($revisionGetterMock, new WikibaseEntityCache(new ArrayCache()));

		$this->setExpectedException('\OutOfRangeException');
		$provider->getProperty(new PropertyId('P42424242'));
	}
}
