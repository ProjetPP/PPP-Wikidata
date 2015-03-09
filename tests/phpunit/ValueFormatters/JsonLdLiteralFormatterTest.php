<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\MonolingualTextValue;
use DataValues\TimeValue;
use PPP\DataModel\JsonLdResourceNode;
use PPP\Wikidata\WikibaseResourceNode;
use ValueFormatters\FormatterOptions;
use ValueFormatters\Test\ValueFormatterTestBase;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLdLiteralFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdLiteralFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		$withTypeFormatter = $this->getMock('PPP\Wikidata\ValueFormatters\JsonLd\JsonLdDataValueFormatter');
		$withTypeFormatter->expects($this->once())
			->method('format')
			->with($this->equalTo(new TimeValue('+00000001952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, '')))
			->will($this->returnValue((object) array( '@type' => 'Date', '@value' => '1952-03-11')));

		$withoutTypeFormatter = $this->getMock('PPP\Wikidata\ValueFormatters\JsonLd\JsonLdDataValueFormatter');
		$withoutTypeFormatter->expects($this->once())
			->method('format')
			->with($this->equalTo(new MonolingualTextValue('en', 'foo')))
			->will($this->returnValue((object) array( '@language' => 'en', '@value' => 'foo')));

		return array(
			array(
				new WikibaseResourceNode(
					'',
					new TimeValue('+00000001952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, '')
				),
				new JsonLdResourceNode(
					'1952-03-11',
					(object) array(
						'@context' => 'http://schema.org',
						'@type' => 'Date',
						'http://www.w3.org/1999/02/22-rdf-syntax-ns#value' => (object) array(
							'@type' => 'Date',
							'@value' => '1952-03-11'
						)
					)
				),
				null,
				new JsonLdLiteralFormatter($withTypeFormatter, new FormatterOptions())
			),
			array(
				new WikibaseResourceNode(
					'',
					new MonolingualTextValue('en', 'foo')
				),
				new JsonLdResourceNode(
					'foo',
					(object) array(
						'@context' => 'http://schema.org',
						'@type' => 'Text',
						'http://www.w3.org/1999/02/22-rdf-syntax-ns#value' => (object) array(
							'@language' => 'en',
							'@value' => 'foo'
						)
					)
				),
				null,
				new JsonLdLiteralFormatter($withoutTypeFormatter, new FormatterOptions())
			),
		);
	}

	/**
	 * @see ValueFormatterTestBase::getFormatterClass
	 *
	 * @return string
	 */
	protected function getFormatterClass() {
		return 'PPP\Wikidata\ValueFormatters\JsonLdLiteralFormatter';
	}
}
