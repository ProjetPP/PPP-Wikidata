<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd;

use DataValues\MonolingualTextValue;
use ValueFormatters\Test\ValueFormatterTestBase;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLd\JsonLdMonolingualTextFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdMonolingualTextFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
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
	 * @see ValueFormatterTestBase::getFormatterClass
	 *
	 * @return string
	 */
	protected function getFormatterClass() {
		return 'PPP\Wikidata\ValueFormatters\JsonLd\JsonLdMonolingualTextFormatter';
	}
}
