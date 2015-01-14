<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\StringValue;
use PPP\DataModel\StringResourceNode;
use ValueFormatters\FormatterOptions;
use ValueFormatters\Test\ValueFormatterTestBase;

/**
 * @covers PPP\Wikidata\ValueFormatters\ToStringFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class ToStringFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new StringValue('foo'),
				new StringResourceNode('foo'),
				null,
				new ToStringFormatter(new \ValueFormatters\StringFormatter(new FormatterOptions()))
			),
		);
	}

	/**
	 * @see ValueFormatterTestBase::getFormatterClass
	 */
	protected function getFormatterClass() {
		return 'PPP\Wikidata\ValueFormatters\ToStringFormatter';
	}
}
