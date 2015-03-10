<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\StringValue;
use ValueFormatters\Test\ValueFormatterTestBase;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLd\DispatchingJsonLdDataValueFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class DispatchingJsonLdDataValueFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		$formatterMock = $this->getMock('PPP\Wikidata\ValueFormatters\JsonLd\JsonLdDataValueFormatter');
		$formatterMock->expects($this->any())
			->method('format')
			->with($this->equalTo(new StringValue('foo')))
			->will($this->returnValue((object) array('@value' => 'foo')));

		return array(
			array(
				new StringValue('foo'),
				(object) array('@value' => 'foo'),
				null,
				$formatterMock
			),
		);
	}

	/**
	 * @see ValueFormatterTestBase::getFormatterClass
	 */
	protected function getFormatterClass() {
		return 'PPP\Wikidata\ValueFormatters\JsonLd\DispatchingJsonLdDataValueFormatter';
	}
}
