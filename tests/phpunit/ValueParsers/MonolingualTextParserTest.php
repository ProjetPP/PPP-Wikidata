<?php

namespace PPP\Wikidata\ValueParsers;

use DataValues\MonolingualTextValue;
use ValueParsers\Test\ValueParserTestBase;

/**
 * @covers PPP\Wikidata\ValueParsers\MonolingualTextParser
 *
 * @licence GPLv2+
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
		return 'PPP\Wikidata\ValueParsers\MonolingualTextParser';
	}
}
