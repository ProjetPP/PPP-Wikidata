<?php

namespace PPP\Wikidata\ValueParsers;

use Mediawiki\Api\MediawikiApi;
use ValueParsers\ParserOptions;
use ValueParsers\Test\ValueParserTestBase;
use ValueParsers\ValueParser;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\EntityStore\Api\ApiEntityStore;

/**
 * @covers PPP\Wikidata\ValueParsers\WikibaseEntityIdParser
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 *
 * @todo mock instead of requests to the real API?
 */
class WikibaseEntityIdParserTest extends ValueParserTestBase {

	/**
	 * @see ValueParserTestBase::validInputProvider
	 */
	public function validInputProvider() {
		return array(
			array(
				'Douglas Adams',
				array(new EntityIdValue(new ItemId('Q42')))
			),
			array(
				'Barack Obama',
				array(new EntityIdValue(new ItemId('Q76')))
			),
			array(
				'P=NP',
				array(new EntityIdValue(new ItemId('Q746242')))
			),
			array(
				'TUNGSTÈNE',
				array(
					new EntityIdValue(new ItemId('Q743')),
					new EntityIdValue(new ItemId('Q3542087'))
				)
			),
			array(
				'Barack Obama',
				array(new EntityIdValue(new ItemId('Q76')))
			),
			array(
				'',
				array()
			),
			array(
				'abcabcabc',
				array()
			),
			array(
				'q42',
				array(new EntityIdValue(new ItemId('Q42')))
			),
			array(
				'VIAF',
				array(new EntityIdValue(new PropertyId('P214'))),
				$this->getInstance('property')
			),
			array(
				'P214',
				array(new EntityIdValue(new PropertyId('P214'))),
				$this->getInstance('property')
			),
		);
	}

	/**
	 * @see ValueParserTestBase::invalidInputProvider
	 */
	public function invalidInputProvider() {
		return array(
			array(
				new ItemId('Q23')
			)
		);
	}

	/**
	 * @see ValueParserTestBase::getInstance
	 */
	protected function getInstance($entityType = 'item') {
		return new WikibaseEntityIdParser(
			new ApiEntityStore(new MediawikiApi('http://www.wikidata.org/w/api.php')),
			$this->newParserOptions($entityType)
		);
	}

	/**
	 * @see ValueParserTestBase::newParserOptions
	 */
	protected function newParserOptions($entityType = 'item') {
		$parserOptions = new ParserOptions();

		$parserOptions->setOption(ValueParser::OPT_LANG, 'fr');
		$parserOptions->setOption(WikibaseEntityIdParser::OPT_ENTITY_TYPE, $entityType);

		return $parserOptions;
	}

	/**
	 * @return ValueParserTestBase::requireDataValue
	 */
	protected function requireDataValue() {
		return false;
	}
}
