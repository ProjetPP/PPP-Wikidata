<?php

namespace PPP\Wikidata\TreeSimplifier;

use Doctrine\Common\Cache\ArrayCache;
use Mediawiki\Api\MediawikiApi;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\StringResourceNode;
use PPP\DataModel\TripleNode;
use PPP\DataModel\UnionNode;
use PPP\Wikidata\Cache\WikibaseEntityCache;
use PPP\Wikidata\ValueParsers\ResourceListNodeParser;
use PPP\Wikidata\ValueParsers\WikibaseValueParserFactory;
use PPP\Wikidata\WikibaseEntityProvider;
use PPP\Wikidata\WikibasePropertyTypeProvider;
use PPP\Wikidata\WikibaseResourceNode;
use Wikibase\Api\WikibaseFactory;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers PPP\Wikidata\TreeSimplifier\WikibaseTripleConverter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 *
 * @todo mock instead of requests to the real API?
 */
class WikibaseTripleConverterTest extends NodeSimplifierBaseTest {

	public function buildSimplifier() {
		$valueParserFactory = new WikibaseValueParserFactory(
			'en',
			new MediawikiApi('http://www.wikidata.org/w/api.php'),
			new ArrayCache()
		);
		$wikibaseFactory = new WikibaseFactory(new MediawikiApi('http://www.wikidata.org/w/api.php'));
		$propertyTypeProvider = new WikibasePropertyTypeProvider(new WikibaseEntityProvider(
			$wikibaseFactory->newRevisionsGetter(),
			new WikibaseEntityCache(new ArrayCache())
		));

		return new WikibaseTripleConverter(new ResourceListNodeParser($valueParserFactory->newWikibaseValueParser()), $propertyTypeProvider);
	}

	public function simplifiableProvider() {
		return array(
			array(
				new TripleNode(
					new MissingNode(),
					new ResourceListNode(array(new StringResourceNode('a'))),
					new ResourceListNode(array())
				)
			),
		);
	}

	public function nonSimplifiableProvider() {
		return array(
			array(
				new MissingNode()
			),
			array(
				new TripleNode(
					new UnionNode(array()),
					new MissingNode(),
					new MissingNode()
				)
			),
		);
	}

	/**
	 * @dataProvider annotatedNodeProvider
	 */
	public function testSimplifyNode(AbstractNode $inputNode, AbstractNode $expectedNode) {
		$this->assertEquals($expectedNode, $this->buildSimplifier()->simplify($inputNode));
	}

	public function annotatedNodeProvider() {
		return array(
			array(
				new TripleNode(
					new ResourceListNode(array(new StringResourceNode('Ramesses III'))),
					new ResourceListNode(array(new StringResourceNode('Place of birth'))),
					new MissingNode()
				),
				new TripleNode(
					new ResourceListNode(array(new WikibaseResourceNode('Ramesses III', new EntityIdValue(new ItemId('Q1528'))))),
					new ResourceListNode(array(new WikibaseResourceNode('Place of birth', new EntityIdValue(new PropertyId('P19'))))),
					new MissingNode()
				)
			),
			array(
				new TripleNode(
					new MissingNode(),
					new ResourceListNode(array(new StringResourceNode('birth name'))),
					new MissingNode()
				),
				new UnionNode(array(
					new TripleNode(
						new MissingNode(),
						new ResourceListNode(array(new WikibaseResourceNode('birth name', new EntityIdValue(new PropertyId('P1477'))))),
						new MissingNode()
					),
					new TripleNode(
						new MissingNode(),
						new ResourceListNode(array(new WikibaseResourceNode('birth name', new EntityIdValue(new PropertyId('P513'))))),
						new MissingNode()
					),
				))
			),
		);
	}
}
