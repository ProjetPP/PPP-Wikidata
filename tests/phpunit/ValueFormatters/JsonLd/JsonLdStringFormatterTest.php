<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd;

use DataValues\StringValue;
use ValueFormatters\FormatterOptions;
use ValueFormatters\Test\ValueFormatterTestBase;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLd\JsonLdStringFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdStringFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new StringValue('foo'),
				(object) array('@value' => 'foo')
			),
		);
	}

	/**
	 * @see ValueFormatterTestBase::getInstance
	 */
	protected function getInstance(FormatterOptions $options = null) {
		return new JsonLdStringFormatter($options);
	}
}
