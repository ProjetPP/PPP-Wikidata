<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd;

use DataValues\Geo\Formatters\GlobeCoordinateFormatter;
use DataValues\Geo\Values\LatLongValue;
use DataValues\GlobeCoordinateValue;
use ValueFormatters\FormatterOptions;
use ValueFormatters\Test\ValueFormatterTestBase;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLd\JsonLdGlobeCoordinateFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdGlobeCoordinateFormatterTest extends ValueFormatterTestBase {

	/**T
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new GlobeCoordinateValue(new LatLongValue(42, 42), 1),
				(object) array(
					'@type' => 'GeoCoordinates',
					'name' => '42, 42',
					'latitude' => 42.0,
					'longitude' => 42.0
				)
			),
		);
	}

	/**
	 * @see ValueFormatterTestBase::getInstance
	 */
	protected function getInstance(FormatterOptions $options = null) {
		return new JsonLdGlobeCoordinateFormatter(new GlobeCoordinateFormatter($options), $options);
	}
}
