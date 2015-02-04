<?php

namespace PPP\Wikidata\TreeSimplifier;

use Mediawiki\Api\MediawikiApi;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\SentenceNode;
use PPP\Wikidata\ValueParsers\ResourceListNodeParser;
use PPP\Wikidata\ValueParsers\WikibaseValueParserFactory;
use PPP\Wikidata\WikibaseResourceNode;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\EntityStore\Api\ApiEntityStore;

/**
 * @covers PPP\Wikidata\TreeSimplifier\SentenceNodeSimplifier
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class SentenceNodeSimplifierTest extends NodeSimplifierBaseTest {

	protected function buildSimplifier() {
		$valueParserFactory = new WikibaseValueParserFactory(
			'en',
			new ApiEntityStore(new MediawikiApi('http://www‡.wikidata.org/w/api.php'))
		);

		return new SentenceNodeSimplifier(new ResourceListNodeParser($valueParserFactory->newWikibaseValueParser()));
	}

	public function simplifiableProvider() {
		return array(
			array(
				new SentenceNode('')
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
				new ResourceListNode(array(new WikibaseResourceNode('Douglas Adams', new EntityIdValue(new ItemId('Q42'))))),
				new SentenceNode('Douglas Adams')
			),
			array(
				new ResourceListNode(),
				new SentenceNode('Who are you?')
			),
		);
	}
}
