<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\MonolingualTextValue;
use PPP\DataModel\StringResourceNode;
use ValueFormatters\Test\ValueFormatterTestBase;

/**
 * @covers PPP\Wikidata\ValueFormatters\MonolingualTextFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class MonolingualTextFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new MonolingualTextValue('en', 'foo'),
				new StringResourceNode('foo', 'en')
			),
		);
	}

	/**
	 * @see ValueFormatterTestBase::getFormatterClass
	 *
	 * @return string
	 */
	protected function getFormatterClass() {
		return 'PPP\Wikidata\ValueFormatters\MonolingualTextFormatter';
	}

}
