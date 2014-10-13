<?php

namespace PPP\Wikidata;

use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers PPP\Wikidata\WikibasePropertyTypeProvider
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibasePropertyTypeProviderTest extends \PHPUnit_Framework_TestCase {

	public function testGetTypeForProperty() {
		$entityProviderMock = $this->getMockBuilder('PPP\Wikidata\WikibaseEntityProvider')
		->disableOriginalConstructor()
		->getMock();
		$entityProviderMock->expects($this->any())
			->method('getProperty')
			->with($this->equalTo(new PropertyId('P42')))
			->will($this->returnValue(Property::newFromType('time')));

		$provider = new WikibasePropertyTypeProvider($entityProviderMock);

		$this->assertEquals('time', $provider->getTypeForProperty(new PropertyId('P42')));
	}
}
