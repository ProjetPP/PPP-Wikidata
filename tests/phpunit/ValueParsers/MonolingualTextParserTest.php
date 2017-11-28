<?php

namespace PPP\Wikidata\ValueParsers;

use DataValues\MonolingualTextValue;
use ValueParsers\ParserOptions;
use ValueParsers\Test\ValueParserTestBase;

/**
 * @covers PPP\Wikidata\ValueParsers\MonolingualTextParser
 *
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class MonolingualTextParserTest extends ValueParserTestBase {

	/**
	 * @see ValueParserTestBase::validInputProvider
	 */
	public function validInputProvider() {
		return array(
			array(
				'Douglas Adams',
				new MonolingualTextValue('en', 'Douglas Adams')
			)
		);
	}

	/**
	 * @see ValueParserTestBase::invalidInputProvider
	 */
	public function invalidInputProvider() {
		return array(
			array(
				false
			)
		);
	}

	/**
	 * @see ValueParserTestBase::getInstance
	 */
	protected function getInstance(ParserOptions $options = null) {
		return new MonolingualTextParser($options);
	}
}
