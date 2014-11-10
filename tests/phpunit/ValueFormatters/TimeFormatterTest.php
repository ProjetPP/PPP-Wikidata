<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\TimeValue;
use PPP\DataModel\TimeResourceNode;
use ValueFormatters\Test\ValueFormatterTestBase;

/**
 * @covers PPP\Wikidata\ValueFormatters\TimeFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class TimeFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new TimeValue('+00000001952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, 'http://www.wikidata.org/entity/Q1985786'),
				new TimeResourceNode('1952-03-11', 'julian')
			),
			array(
				new TimeValue('-00000001952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, ''),
				new TimeResourceNode('-1952-03-11', 'gregorian')
			),
			array(
				new TimeValue('+00000001952-03-11T01:01:01Z', 90, 0, 0, TimeValue::PRECISION_YEAR, ''),
				new TimeResourceNode('1952', 'gregorian')
			),
			array(
				new TimeValue('+00000001952-03-11T01:01:01Z', 90, 0, 0, TimeValue::PRECISION_MONTH, ''),
				new TimeResourceNode('1952-03', 'gregorian')
			),
			array(
				new TimeValue('+00000001952-03-11T01:01:01Z', 90, 0, 0, TimeValue::PRECISION_DAY, ''),
				new TimeResourceNode('1952-03-11', 'gregorian')
			),
			array(
				new TimeValue('+00000001952-03-11T01:01:01Z', 90, 0, 0, TimeValue::PRECISION_HOUR, ''),
				new TimeResourceNode('1952-03-11T01+01:30', 'gregorian')
			),
			array(
				new TimeValue('+00000001952-03-11T01:01:01Z', 90, 0, 0, TimeValue::PRECISION_MINUTE, ''),
				new TimeResourceNode('1952-03-11T01:01+01:30', 'gregorian')
			),
			array(
				new TimeValue('+00000001952-03-11T01:01:01Z', 90, 0, 0, TimeValue::PRECISION_SECOND, ''),
				new TimeResourceNode('1952-03-11T01:01:01+01:30', 'gregorian')
			),
			array(
				new TimeValue('+00000001952-03-11T01:01:01Z', -90, 0, 0, TimeValue::PRECISION_SECOND, ''),
				new TimeResourceNode('1952-03-11T01:01:01-01:30', 'gregorian')
			),
		);
	}

	/**
	 * @see ValueFormatterTestBase::getFormatterClass
	 *
	 * @return string
	 */
	protected function getFormatterClass() {
		return 'PPP\Wikidata\ValueFormatters\TimeFormatter';
	}

}
