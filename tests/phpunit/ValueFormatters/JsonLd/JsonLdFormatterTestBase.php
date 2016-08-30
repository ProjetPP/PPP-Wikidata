<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd;

use ValueFormatters\FormatterOptions;
use ValueFormatters\Test\ValueFormatterTestBase;
use ValueFormatters\ValueFormatter;

/**
 * @license GPL-2.0+
 * @author Thomas Pellissier Tanon
 */
abstract class JsonLdFormatterTestBase extends ValueFormatterTestBase {

	/**
	 * @see JsonLdFormatterTestBase::testValidFormat
	 *
	 * @dataProvider validProvider
	 */
	public function testValidFormat(
		$value,
		$expected,
		FormatterOptions $options = null,
		ValueFormatter $formatter = null
	) {
		if ( $formatter === null ) {
			$formatter = $this->getInstance( $options );
		}

		$this->assertEquals( $expected, $formatter->format( $value ) );
	}

}
