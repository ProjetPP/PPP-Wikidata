<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\Geo\Values\LatLongValue;
use DataValues\GlobeCoordinateValue;
use PPP\DataModel\JsonLdResourceNode;
use ValueFormatters\Test\ValueFormatterTestBase;

/**
 * @covers PPP\Wikidata\ValueFormatters\GlobeCoordinateFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class GlobeCoordinateFormatterTest extends ValueFormatterTestBase {

	/**T
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new GlobeCoordinateValue(new LatLongValue(42, 42), 1),
				new JsonLdResourceNode(
					'42, 42',
					(object) array(
						'@context' => 'http://schema.org',
                        '@type' => 'GeoCoordinates',
                        'latitude' => 42.0,
						'longitude' => 42.0
					)
				)
			),
		);
	}

	/**
	 * @see ValueFormatterTestBase::getFormatterClass
	 *
	 * @return string
	 */
	protected function getFormatterClass() {
		return 'PPP\Wikidata\ValueFormatters\GlobeCoordinateFormatter';
	}

}
