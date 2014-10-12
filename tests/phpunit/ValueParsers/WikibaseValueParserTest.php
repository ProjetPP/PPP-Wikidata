<?php

namespace PPP\Wikidata\ValueParsers;

use DataValues\BooleanValue;
use ValueParsers\BoolParser;

/**
 * @covers PPP\Wikidata\ValueParsers\WikibaseValueParser
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseValueParserTest extends \PHPUnit_Framework_TestCase {

	public function testParse() {
		$parser = new WikibaseValueParser(array(
			'bool' => new BoolParser()
		));
		$this->assertEquals(new BooleanValue(true), $parser->parse('true', 'bool'));
	}

	public function testParseWithUnknownType() {
		$parser = new WikibaseValueParser(array());

		$this->setExpectedException('ValueParsers\ParseException');
		$parser->parse('true', 'bool');
	}
}
