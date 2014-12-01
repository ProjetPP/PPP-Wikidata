<?php

namespace PPP\Wikidata;

use DataValues\UnknownValue;
use Doctrine\Common\Cache\ArrayCache;
use Mediawiki\Api\MediawikiApi;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\StringResourceNode;
use PPP\DataModel\TripleNode;
use PPP\DataModel\UnionNode;
use PPP\Wikidata\Cache\WikibaseEntityCache;
use PPP\Wikidata\ValueParsers\WikibaseValueParserFactory;
use Wikibase\Api\WikibaseFactory;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers PPP\Wikidata\WikibaseNodeAnnotator
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 *
 * @todo mock instead of requests to the real API?
 * @todo object for multiple types
 */
class WikibaseNodeAnnotatorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider annotatedNodeProvider
	 */
	public function testAnnotateNode(AbstractNode $inputNode, AbstractNode $expectedNode) {
		$valueParserFactory = new WikibaseValueParserFactory(
			'en',
			new MediawikiApi('http://www.wikidata.org/w/api.php'),
			new ArrayCache()
		);
		$wikibaseFactory = new WikibaseFactory(new MediawikiApi('http://www.wikidata.org/w/api.php'));
		$propertyTypeProvider = new WikibasePropertyTypeProvider(new WikibaseEntityProvider(
			$wikibaseFactory->newRevisionGetter(),
			new WikibaseEntityCache(new ArrayCache())
		));

		$annotator = new WikibaseNodeAnnotator($valueParserFactory->newWikibaseValueParser(), $propertyTypeProvider);
		$this->assertEquals($expectedNode, $annotator->annotateNode($inputNode));
	}

	public function annotatedNodeProvider() {
		return array(
			array(
				new ResourceListNode(array(new StringResourceNode('Douglas Adams'))),
				new ResourceListNode(array(new WikibaseResourceNode('Douglas Adams', new UnknownValue(new StringResourceNode('Douglas Adams')))))
			),
			array(
				new TripleNode(
					new ResourceListNode(array(new StringResourceNode('Ramesses III'))),
					new ResourceListNode(array(new StringResourceNode('Place of birth'))),
					new MissingNode()
				),
				new UnionNode(array(new TripleNode(
					new ResourceListNode(array(new WikibaseResourceNode('Ramesses III', new EntityIdValue(new ItemId('Q1528'))))),
					new ResourceListNode(array(new WikibaseResourceNode('Place of birth', new EntityIdValue(new PropertyId('P19'))))),
					new MissingNode()
				)))
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
