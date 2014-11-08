<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\StringValue;
use PPP\DataModel\StringResourceNode;
use ValueFormatters\Test\ValueFormatterTestBase;

/**
 * @covers PPP\Wikidata\ValueFormatters\StringFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class StringFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new StringValue('foo'),
				new StringResourceNode('foo')
			),
		);
	}

	/**
	 * @see ValueFormatterTestBase::getFormatterClass
	 *
	 * @return string
	 */
	protected function getFormatterClass() {
		return 'PPP\Wikidata\ValueFormatters\StringFormatter';
	}

}
