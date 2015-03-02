<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\TimeValue;
use ValueFormatters\Test\ValueFormatterTestBase;

/**
 * @covers PPP\Wikidata\ValueFormatters\IsoTimeFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class IsoTimeFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new TimeValue('+00000001952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, 'http://www.wikidata.org/entity/Q1985786'),
				'1952-03-11'
			),
			array(
				new TimeValue('-00000001952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, ''),
				'-1952-03-11'
			),
			array(
				new TimeValue('+00000001952-03-11T01:01:01Z', 90, 0, 0, TimeValue::PRECISION_YEAR, ''),
				'1952'
			),
			array(
				new TimeValue('+00000001952-03-11T01:01:01Z', 90, 0, 0, TimeValue::PRECISION_MONTH, ''),
				'1952-03'
			),
			array(
				new TimeValue('+00000001952-03-11T01:01:01Z', 90, 0, 0, TimeValue::PRECISION_DAY, ''),
				'1952-03-11'
			),
			array(
				new TimeValue('+00000001952-03-11T01:01:01Z', 90, 0, 0, TimeValue::PRECISION_HOUR, ''),
				'1952-03-11T01+01:30'
			),
			array(
				new TimeValue('+00000001952-03-11T01:01:01Z', 90, 0, 0, TimeValue::PRECISION_MINUTE, ''),
				'1952-03-11T01:01+01:30'
			),
			array(
				new TimeValue('+00000001952-03-11T01:01:01Z', 90, 0, 0, TimeValue::PRECISION_SECOND, ''),
				'1952-03-11T01:01:01+01:30'
			),
			array(
				new TimeValue('+00000001952-03-11T01:01:01Z', -90, 0, 0, TimeValue::PRECISION_SECOND, ''),
				'1952-03-11T01:01:01-01:30'
			),
		);
	}

	/**
	 * @see ValueFormatterTestBase::getFormatterClass
	 *
	 * @return string
	 */
	protected function getFormatterClass() {
		return 'PPP\Wikidata\ValueFormatters\IsoTimeFormatter';
	}
}
