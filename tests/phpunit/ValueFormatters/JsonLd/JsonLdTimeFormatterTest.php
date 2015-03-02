<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd;

use DataValues\TimeValue;
use PPP\Wikidata\ValueFormatters\IsoTimeFormatter;
use ValueFormatters\FormatterOptions;
use ValueFormatters\Test\ValueFormatterTestBase;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLd\JsonLdTimeFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdTimeFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new TimeValue('+00000001952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, 'http://www.wikidata.org/entity/Q1985786'),
				(object) array('@type' => 'Date', '@value' => '1952-03-11')
			),
			array(
				new TimeValue('+00000001952-03-11T01:01:01Z', 90, 0, 0, TimeValue::PRECISION_SECOND, ''),
				(object) array('@type' => 'DateTime', '@value' => '1952-03-11T01:01:01+01:30')
			),
		);
	}

	/**
	 * @see ValueFormatterTestBase::getFormatterClass
	 *
	 * @return string
	 */
	protected function getFormatterClass() {
		return 'PPP\Wikidata\ValueFormatters\JsonLd\JsonLdTimeFormatter';
	}

	/**
	 * @see ValueFormatterTestBase::getInstance
	 */
	protected function getInstance(FormatterOptions $options) {
		$class = $this->getFormatterClass();

	return new $class(
			new IsoTimeFormatter($options),
			$options
		);
	}
}
