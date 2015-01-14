<?php

namespace PPP\Wikidata;

use DataValues\StringValue;
use Doctrine\Common\Cache\ArrayCache;
use Mediawiki\Api\MediawikiApi;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\StringResourceNode;
use PPP\Module\TreeSimplifier\NodeSimplifierBaseTest;
use PPP\Wikidata\ValueFormatters\WikibaseResourceNodeFormatterFactory;

/**
 * @covers PPP\Wikidata\ResourceListNodeFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class ResourceListNodeFormatterTest extends NodeSimplifierBaseTest {

	protected function buildSimplifier() {
		$valueParserFactory = new WikibaseResourceNodeFormatterFactory(
			'en',
			new MediawikiApi('http://www.wikidata.org/w/api.php'),
			array(
				'enwiki' => new MediawikiApi('http://en.wikipedia.org/w/api.php'),
			),
			new ArrayCache()
		);

		$entityIdFormatterPreloaderMock = $this->getMockBuilder('PPP\Wikidata\ValueFormatters\WikibaseEntityIdFormatterPreloader')
			->disableOriginalConstructor()
			->getMock();
		$entityIdFormatterPreloaderMock->expects($this->any())
			->method('preload');

		return new ResourceListNodeFormatter($valueParserFactory->newWikibaseResourceNodeFormatter(), $entityIdFormatterPreloaderMock);
	}

	public function simplifiableProvider() {
		return array(
			array(
				new ResourceListNode()
			)
		);
	}

	public function nonSimplifiableProvider() {
		return array(
			array(
				new MissingNode()
			)
		);
	}

	public function simplificationProvider() {
		return array(
			array(
				new ResourceListNode(),
				new ResourceListNode()
			),
			array(
				new ResourceListNode(array(
					new StringResourceNode('Douglas Adam'),
					new StringResourceNode('foo')
				)),
				new ResourceListNode(array(
					new WikibaseResourceNode('', new StringValue('Douglas Adam')),
					new StringResourceNode('foo')
				))
			),
		);
	}
}
