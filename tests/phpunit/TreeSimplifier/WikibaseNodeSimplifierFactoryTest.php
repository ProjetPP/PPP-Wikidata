<?php

namespace PPP\Wikidata\TreeSimplifier;

use Doctrine\Common\Cache\ArrayCache;

/**
 * @covers PPP\Wikidata\TreeSimplifier\WikibaseNodeSimplifierFactory
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseNodeSimplifierFactoryTest extends \PHPUnit_Framework_TestCase {

	public function testNewSentenceTreeSimplifier() {
		$mediawikiApiMock = $this->getMockBuilder('Mediawiki\Api\MediawikiApi')
			->disableOriginalConstructor()
			->getMock();
		$wikidataQueryApiMock = $this->getMockBuilder('WikidataQueryApi\WikidataQueryApi')
			->disableOriginalConstructor()
			->getMock();
		$factory = new WikibaseNodeSimplifierFactory($mediawikiApiMock, $wikidataQueryApiMock, new ArrayCache(), 'en');

		$this->assertInstanceOf(
			'PPP\Module\TreeSimplifier\NodeSimplifier',
			$factory->newNodeSimplifier()
		);
	}
}
