<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd;

use DataValues\MonolingualTextValue;
use ValueFormatters\FormatterOptions;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLd\JsonLdMonolingualTextFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdMonolingualTextFormatterTest extends JsonLdFormatterTestBase {

	/**
	 * @see JsonLdFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new MonolingualTextValue('en', 'foo'),
				(object) array('@language' => 'en', '@value' => 'foo')
			),
		);
	}

	/**
	 * @see JsonLdFormatterTestBase::getInstance
	 */
	protected function getInstance(FormatterOptions $options = null) {
		return new JsonLdMonolingualTextFormatter($options);
	}
}
