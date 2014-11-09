<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\UnknownValue;
use PPP\DataModel\StringResourceNode;
use ValueFormatters\Test\ValueFormatterTestBase;

/**
 * @covers PPP\Wikidata\ValueFormatters\UnknownFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class UnknownFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new UnknownValue('foo'),
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
		return 'PPP\Wikidata\ValueFormatters\UnknownFormatter';
	}

}
