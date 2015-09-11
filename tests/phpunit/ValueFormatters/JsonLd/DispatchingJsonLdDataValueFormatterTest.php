<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd;

use DataValues\StringValue;
use ValueFormatters\FormatterOptions;
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
	 * @see ValueFormatterTestBase::getInstance
	 */
	protected function getInstance(FormatterOptions $options = null) {
		return null;
	}
}
