<?php

namespace PPP\Wikidata\TreeSimplifier;

use Doctrine\Common\Cache\ArrayCache;
use Mediawiki\Api\MediawikiApi;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\SentenceNode;
use PPP\DataModel\StringResourceNode;
use PPP\DataModel\TripleNode;
use PPP\Module\TreeSimplifier\NodeSimplifierBaseTest;
use PPP\Wikidata\Cache\WikibaseEntityCache;
use PPP\Wikidata\ValueParsers\ResourceListNodeParser;
use PPP\Wikidata\ValueParsers\WikibaseValueParserFactory;
use PPP\Wikidata\WikibaseEntityProvider;
use Wikibase\Api\WikibaseFactory;

/**
 * @covers PPP\Wikidata\TreeSimplifier\IdentityTripleNodeSimplifier
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class IdentityTripleNodeSimplifierTest extends NodeSimplifierBaseTest {

	public function buildSimplifier() {
		$valueParserFactory = new WikibaseValueParserFactory(
			'fr',
			new MediawikiApi('http://www.wikidata.org/w/api.php'),
			new ArrayCache()
		);
		$wikibaseFactory = new WikibaseFactory(new MediawikiApi('http://www.wikidata.org/w/api.php'));

		return new IdentityTripleNodeSimplifier(
			new ResourceListNodeParser($valueParserFactory->newWikibaseValueParser()),
			new WikibaseEntityProvider(
				$wikibaseFactory->newRevisionGetter(),
				new WikibaseEntityCache(new ArrayCache())
			),
			'fr'
		);
	}

	/**
	 * @see NodeSimplifierBaseTest::simplifiableProvider
	 */
	public function simplifiableProvider() {
		return array(
			array(
				new TripleNode(
					new ResourceListNode(array(new StringResourceNode('Léon de la Brière'))),
					new ResourceListNode(array(new StringResourceNode('Identity'))),
					new MissingNode()
				)
			),
			array(
				new SentenceNode('Léon de la Brière')
			)
		);
	}

	/**
	 * @see NodeSimplifierBaseTest::nonSimplifiableProvider
	 */
	public function nonSimplifiableProvider() {
		return array(
			array(
				new MissingNode()
			),
			array(
				new TripleNode(
					new MissingNode(),
					new ResourceListNode(array(new StringResourceNode('foo'))),
					new ResourceListNode(array(new StringResourceNode('bar')))
				)
			),
			array(
				new TripleNode(
					new ResourceListNode(array(new StringResourceNode('foo'))),
					new MissingNode(),
					new ResourceListNode(array(new StringResourceNode('bar')))
				)
			),
		);
	}

	public function simplificationProvider() {
		return array(
			array(
				new ResourceListNode(array(new StringResourceNode('Léon Leroy de la Brière (14 janvier 1845 - 12 septembre 1899) est un écrivain politique français de la fin du XIXe siècle.'))),
				new SentenceNode('Léon de la Brière')
			),
			array(
				new ResourceListNode(),
				new SentenceNode('Lowendal')
			),
			array(
				new ResourceListNode(array(new StringResourceNode('Léon Leroy de la Brière (14 janvier 1845 - 12 septembre 1899) est un écrivain politique français de la fin du XIXe siècle.'))),
				new TripleNode(
					new ResourceListNode(array(new StringResourceNode('Léon de la Brière'))),
					new ResourceListNode(array(new StringResourceNode('Identity'))),
					new MissingNode()
				)
			),
			array(
				new TripleNode(
					new ResourceListNode(array(new StringResourceNode('Léon de la Brière'))),
					new ResourceListNode(array(new StringResourceNode('Identities'))),
					new MissingNode()
				),
				new TripleNode(
					new ResourceListNode(array(new StringResourceNode('Léon de la Brière'))),
					new ResourceListNode(array(new StringResourceNode('Identities'))),
					new MissingNode()
				)
			),
		);
	}
}
