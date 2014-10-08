<?php

namespace PPP\Wikidata\ValueParsers;

use Mediawiki\Api\MediawikiApi;
use ValueParsers\Test\ValueParserTestBase;
use ValueParsers\ValueParser;
use Wikibase\DataModel\Deserializers\EntityIdDeserializer;
use Wikibase\DataModel\Entity\BasicEntityIdParser;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers PPP\Wikidata\ValueParsers\WikibaseEntityParser
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 *
 * @todo mock instead of requests to the real API?
 */
class WikibaseEntityParserTest extends ValueParserTestBase {

	/**
	 * @see ValueParserTestBase::validInputProvider
	 */
	public function validInputProvider() {
		return array(
			array(
				'Douglas Adams',
				new ItemId('Q42')
			)
		);
	}

	/**
	 * @see ValueParserTestBase::invalidInputProvider
	 */
	public function invalidInputProvider() {
		return parent::invalidInputProvider() + array(
			array(
				'abcabc'
			)
		);
	}

	/**
	 * @see ValueParserTestBase::getParserClass
	 */
	protected function getParserClass() {
		return 'PPP\Wikidata\ValueParsers\WikibaseEntityParser';
	}


	/**
	 * @see ValueParserTestBase::getInstance
	 */
	protected function getInstance() {
		$class = $this->getParserClass();
		return new $class(
			new MediawikiApi('http://www.wikidata.org/w/api.php'),
			new EntityIdDeserializer(new BasicEntityIdParser()),
			$this->newParserOptions()
		);
	}

	/**
	 * @see ValueParserTestBase::newParserOptions
	 */
	protected function newParserOptions() {
		$parserOptions = parent::newParserOptions();

		$parserOptions->setOption(ValueParser::OPT_LANG, 'fr');
		$parserOptions->setOption(WikibaseEntityParser::OPT_ENTITY_TYPE, 'item');

		return $parserOptions;
	}
}
