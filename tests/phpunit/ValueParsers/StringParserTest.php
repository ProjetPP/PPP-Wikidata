<?php

namespace PPP\Wikidata\ValueParsers;

use DataValues\StringValue;
use ValueParsers\Test\ValueParserTestBase;

/**
 * @covers PPP\Wikidata\ValueParsers\StringParser
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class StringParserTest extends ValueParserTestBase {

	/**
	 * @see ValueParserTestBase::validInputProvider
	 */
	public function validInputProvider() {
		return array(
			array(
				'Douglas Adams',
				new StringValue('Douglas Adams')
			)
		);
	}

	/**
	 * @see ValueParserTestBase::invalidInputProvider
	 */
	public function invalidInputProvider() {
		return parent::invalidInputProvider() + array(
			array(
				false
			)
		);
	}

	/**
	 * @see ValueParserTestBase::getParserClass
	 */
	protected function getParserClass() {
		return 'PPP\Wikidata\ValueParsers\StringParser';
	}
}
