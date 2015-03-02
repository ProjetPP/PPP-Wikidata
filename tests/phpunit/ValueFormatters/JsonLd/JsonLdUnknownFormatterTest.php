<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\UnknownValue;
use ValueFormatters\Test\ValueFormatterTestBase;

/**
 * @covers PPP\Wikidata\ValueFormatters\UnknownFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdUnknownFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new UnknownValue('foo'),
				(object) array('@value' => 'foo')
			),
		);
	}

	/**
	 * @see ValueFormatterTestBase::getFormatterClass
	 *
	 * @return string
	 */
	protected function getFormatterClass() {
		return 'PPP\Wikidata\ValueFormatters\JsonLd\JsonLdUnknownFormatter';
	}
}
