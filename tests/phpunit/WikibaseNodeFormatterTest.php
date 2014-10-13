<?php

namespace PPP\Wikidata;

use DataValues\StringValue;
use Mediawiki\Api\MediawikiApi;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\TripleNode;
use PPP\Wikidata\ValueFormatters\WikibaseValueFormatterFactory;

/**
 * @covers PPP\Wikidata\WikibaseNodeFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 *
 * @todo mock instead of requests to the real API?
 */
class WikibaseNodeFormatterTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider formattedNodeProvider
	 */
	public function testAnnotateNode(AbstractNode $inputNode, AbstractNode $expectedNode) {
		$valueParserFactory = new WikibaseValueFormatterFactory('en', new MediawikiApi('http://www.wikidata.org/w/api.php'));

		$formatter = new WikibaseNodeFormatter($valueParserFactory->newWikibaseValueFormatter());
		$this->assertEquals($expectedNode, $formatter->formatNode($inputNode));
	}

	public function formattedNodeProvider() {
		return array(
			array(
				new WikibaseResourceNode('', new StringValue('Douglas Adam')),
				new WikibaseResourceNode('Douglas Adam', new StringValue('Douglas Adam'))
			),
			array(
				new TripleNode(
					new WikibaseResourceNode('', new StringValue('Douglas Adam')),
					new WikibaseResourceNode('Place of birth', new StringValue('Place of birth')),
					new MissingNode()
				),
				new TripleNode(
					new WikibaseResourceNode('Douglas Adam', new StringValue('Douglas Adam')),
					new WikibaseResourceNode('Place of birth', new StringValue('Place of birth')),
					new MissingNode()
				)
			),
		);
	}
}
