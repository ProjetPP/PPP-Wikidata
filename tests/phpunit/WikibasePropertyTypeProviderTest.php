<?php

namespace PPP\Wikidata;

use Mediawiki\Api\MediawikiApi;
use Wikibase\Api\WikibaseFactory;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers PPP\Wikidata\WikibasePropertyTypeProvider
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibasePropertyTypeProviderTest extends \PHPUnit_Framework_TestCase {

	public function testGetTypeForProperty() {
		$wikibaseFactory = new WikibaseFactory(new MediawikiApi('http://www.wikidata.org/w/api.php'));
		$provider = new WikibasePropertyTypeProvider($wikibaseFactory->newRevisionGetter());

		$this->assertEquals('time', $provider->getTypeForProperty(new PropertyID('P569')));
	}

	public function testGetTypeForPropertyWithException() {
		$wikibaseFactory = new WikibaseFactory(new MediawikiApi('http://www.wikidata.org/w/api.php'));
		$provider = new WikibasePropertyTypeProvider($wikibaseFactory->newRevisionGetter());

		$this->setExpectedException('\OutOfRangeException');
		$provider->getTypeForProperty(new PropertyId('P42424242'));
	}
}
