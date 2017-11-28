<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd;

use DataValues\DecimalValue;
use DataValues\QuantityValue;
use ValueFormatters\DecimalFormatter;
use ValueFormatters\FormatterOptions;
use ValueFormatters\QuantityFormatter;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLd\JsonLdQuantityFormatter
 *
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class JsonLdQuantityFormatterTest extends JsonLdFormatterTestBase {

	/**T
	 * @see JsonLdFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new QuantityValue(new DecimalValue(1234), '1', new DecimalValue(1235), new DecimalValue(1233.3333)),
				(object) array(
					'@type' => 'QuantitativeValue',
					'name' => '1234.0±1.0',
					'value' => (object) array('@type' => 'Integer', '@value' => 1234),
					'minValue' => (object) array('@type' => 'Float', '@value' => 1233.3333),
					'maxValue' => (object) array('@type' => 'Integer', '@value' => 1235),
				)
			),
			array(
				new QuantityValue(new DecimalValue(1234), 'http://www.wikidata.org/entity/Q11573', new DecimalValue(1234), new DecimalValue(1234)),
				(object) array(
					'@type' => 'QuantitativeValue',
					'name' => '1234 m',
					'value' => (object) array('@type' => 'Integer', '@value' => 1234),
					'minValue' => (object) array('@type' => 'Integer', '@value' => 1234),
					'maxValue' => (object) array('@type' => 'Integer', '@value' => 1234),
					'unitCode' => 'http://www.wikidata.org/entity/Q11573'
				)
			),
		);
	}

	/**
	 * @see JsonLdFormatterTestBase::getInstance
	 */
	protected function getInstance(FormatterOptions $options = null) {
		$vocabularyUriFormatter = $this->getMock('\ValueFormatters\ValueFormatter');
		$vocabularyUriFormatter->expects($this->any())
			->method('format')
			->with($this->equalTo('http://www.wikidata.org/entity/Q11573'))
			->will($this->returnValue('m'));

		return new JsonLdQuantityFormatter(
			new QuantityFormatter($options, new DecimalFormatter($options), $vocabularyUriFormatter, '$1 $2'),
			new JsonLdDecimalFormatter($options),
			$options
		);
	}
}
