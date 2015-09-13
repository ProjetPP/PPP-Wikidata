<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd;

use DataValues\DecimalValue;
use ValueFormatters\FormatterOptions;
use ValueFormatters\Test\ValueFormatterTestBase;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLd\JsonLdDecimalFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdDecimalFormatterTest extends ValueFormatterTestBase {

	/**T
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new DecimalValue(123000),
				(object) array(
					'@type' => 'Integer',
					'@value' => 123000
				)
			),
			array(
				new DecimalValue(123000.3333),
				(object) array(
					'@type' => 'Float',
					'@value' => 123000.3333
				)
			),
		);
	}

	/**
	 * @see ValueFormatterTestBase::getInstance
	 */
	protected function getInstance(FormatterOptions $options = null) {
		return new JsonLdDecimalFormatter($options);
	}
}
