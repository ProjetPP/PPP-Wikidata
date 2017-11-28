<?php

namespace PPP\Wikidata\TreeSimplifier;

/**
 * @covers PPP\Wikidata\TreeSimplifier\WikibaseNodeSimplifierFactory
 *
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class WikibaseNodeSimplifierFactoryTest extends \PHPUnit_Framework_TestCase {

	public function testNewSentenceTreeSimplifier() {
		$entityStoreMock = $this->getMock('Wikibase\EntityStore\EntityStore');
		$factory = new WikibaseNodeSimplifierFactory($entityStoreMock, 'en');

		$this->assertInstanceOf(
			'PPP\Module\TreeSimplifier\NodeSimplifier',
			$factory->newNodeSimplifier()
		);
	}
}
