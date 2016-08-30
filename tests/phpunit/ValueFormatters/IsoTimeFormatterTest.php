<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\TimeValue;
use ValueFormatters\FormatterOptions;
use ValueFormatters\Test\ValueFormatterTestBase;

/**
 * @covers PPP\Wikidata\ValueFormatters\IsoTimeFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class IsoTimeFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see JsonLdFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new TimeValue('+1952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, 'http://www.wikidata.org/entity/Q1985786'),
				'1952-03-11'
			),
			array(
				new TimeValue('-1952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, 'http://www.wikidata.org/entity/Q1985786'),
				'-1952-03-11'
			),
			array(
				new TimeValue('+1952-03-11T01:01:01Z', 90, 0, 0, TimeValue::PRECISION_YEAR, 'http://www.wikidata.org/entity/Q1985786'),
				'1952'
			),
			array(
				new TimeValue('+1952-03-11T01:01:01Z', 90, 0, 0, TimeValue::PRECISION_MONTH, 'http://www.wikidata.org/entity/Q1985786'),
				'1952-03'
			),
			array(
				new TimeValue('+1952-03-11T01:01:01Z', 90, 0, 0, TimeValue::PRECISION_DAY, 'http://www.wikidata.org/entity/Q1985786'),
				'1952-03-11'
			),
			array(
				new TimeValue('+1952-03-11T01:01:01Z', 90, 0, 0, TimeValue::PRECISION_HOUR, 'http://www.wikidata.org/entity/Q1985786'),
				'1952-03-11T01+01:30'
			),
			array(
				new TimeValue('+1952-03-11T01:01:01Z', 90, 0, 0, TimeValue::PRECISION_MINUTE, 'http://www.wikidata.org/entity/Q1985786'),
				'1952-03-11T01:01+01:30'
			),
			array(
				new TimeValue('+1952-03-11T01:01:01Z', 90, 0, 0, TimeValue::PRECISION_SECOND, 'http://www.wikidata.org/entity/Q1985786'),
				'1952-03-11T01:01:01+01:30'
			),
			array(
				new TimeValue('+1952-03-11T01:01:01Z', -90, 0, 0, TimeValue::PRECISION_SECOND, 'http://www.wikidata.org/entity/Q1985786'),
				'1952-03-11T01:01:01-01:30'
			),
		);
	}

	/**
	 * @see JsonLdFormatterTestBase::getInstance
	 */
	protected function getInstance(FormatterOptions $options = null) {
		return new IsoTimeFormatter($options);
	}
}
