<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd;

use DataValues\TimeValue;
use PPP\Wikidata\ValueFormatters\IsoTimeFormatter;
use ValueFormatters\FormatterOptions;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLd\JsonLdTimeFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdTimeFormatterTest extends JsonLdFormatterTestBase {

	/**
	 * @see JsonLdFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new TimeValue('+00000001952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, 'http://www.wikidata.org/entity/Q1985786'),
				(object) array('@type' => 'Date', '@value' => '1952-03-11')
			),
			array(
				new TimeValue('+00000001952-03-11T01:01:01Z', 90, 0, 0, TimeValue::PRECISION_SECOND, 'http://www.wikidata.org/entity/Q1985786'),
				(object) array('@type' => 'DateTime', '@value' => '1952-03-11T01:01:01+01:30')
			),
		);
	}

	/**
	 * @see JsonLdFormatterTestBase::getInstance
	 */
	protected function getInstance(FormatterOptions $options = null) {
		return new JsonLdTimeFormatter(
			new IsoTimeFormatter($options),
			$options
		);
	}
}
