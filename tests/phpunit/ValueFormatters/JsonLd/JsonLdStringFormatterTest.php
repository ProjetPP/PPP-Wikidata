<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd;

use DataValues\StringValue;
use ValueFormatters\FormatterOptions;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLd\JsonLdStringFormatter
 *
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class JsonLdStringFormatterTest extends JsonLdFormatterTestBase {

	/**
	 * @see JsonLdFormatterTestBase::validProvider
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
	 * @see JsonLdFormatterTestBase::getInstance
	 */
	protected function getInstance(FormatterOptions $options = null) {
		return new JsonLdStringFormatter($options);
	}
}
