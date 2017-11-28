<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd\Entity;

use Wikibase\DataModel\Entity\BasicEntityIdParser;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLd\Entity\EntityIriParser
 *
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class EntityIriParserTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider entityIriProvider
	 */
	public function testParse($iri, EntityId $expected) {
		$parser = new EntityIriParser(new BasicEntityIdParser());

		$this->assertEquals($expected, $parser->parse($iri));
	}

	public function entityIriProvider() {
		return array(
			array('http://www.wikidata.org/entity/q1', new ItemId('q1')),
			array('https://www.wikidata.org/entity/P1', new PropertyId('P1'))
		);
	}

	/**
	 * @dataProvider invalidEntityIriProvider
	 */
	public function testParseWithException($badIri) {
		$parser = new EntityIriParser(new BasicEntityIdParser());

		$this->setExpectedException('Wikibase\DataModel\Entity\EntityIdParsingException');
		$parser->parse($badIri);
	}

	public function invalidEntityIriProvider() {
		return array(
			array('http://foo.org'),
			array('https://www.wikidata.org/entity/foo')
		);
	}
}
