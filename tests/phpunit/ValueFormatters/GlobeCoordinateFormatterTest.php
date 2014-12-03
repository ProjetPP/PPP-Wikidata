<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\Geo\Values\LatLongValue;
use DataValues\GlobeCoordinateValue;
use GeoJson\Geometry\Point;
use PPP\DataModel\GeoJsonResourceNode;
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
				new GeoJsonResourceNode('42, 42', new Point(array(42, 42)))
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
