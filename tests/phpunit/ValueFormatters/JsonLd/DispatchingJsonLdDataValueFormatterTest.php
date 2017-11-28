<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd;

use DataValues\StringValue;
use ValueFormatters\FormatterOptions;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLd\DispatchingJsonLdDataValueFormatter
 *
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class DispatchingJsonLdDataValueFormatterTest extends JsonLdFormatterTestBase {

	/**
	 * @see JsonLdFormatterTestBase::validProvider
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
	 * @see JsonLdFormatterTestBase::getInstance
	 */
	protected function getInstance(FormatterOptions $options = null) {
		return null;
	}
}
