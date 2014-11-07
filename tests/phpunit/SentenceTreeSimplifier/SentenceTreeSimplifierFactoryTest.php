<?php

namespace PPP\Wikidata\SentenceTreeSimplifier;

use Doctrine\Common\Cache\ArrayCache;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceNode;

/**
 * @covers PPP\Wikidata\SentenceTreeSimplifier\SentenceTreeSimplifierFactory
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class SentenceTreeSimplifierFactoryTest extends \PHPUnit_Framework_TestCase {

	public function testNewSentenceTreeSimplifier() {
		$mediawikiApiMock = $this->getMockBuilder('Mediawiki\Api\MediawikiApi')
			->disableOriginalConstructor()
			->getMock();
		$wikidataQueryApiMock = $this->getMockBuilder('WikidataQueryApi\WikidataQueryApi')
			->disableOriginalConstructor()
			->getMock();
		$factory = new SentenceTreeSimplifierFactory($mediawikiApiMock, $wikidataQueryApiMock, new ArrayCache());

		$this->assertInstanceOf(
			'PPP\Wikidata\SentenceTreeSimplifier\SentenceTreeSimplifier',
			$factory->newSentenceTreeSimplifier()
		);
	}
}
