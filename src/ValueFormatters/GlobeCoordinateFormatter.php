<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\Geo\Values\GlobeCoordinateValue;
use GeoJson\Geometry\Point;
use InvalidArgumentException;
use PPP\DataModel\GeoJsonResourceNode;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;
use ValueFormatters\ValueFormatterBase;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 * @todo support globes
 */
class GlobeCoordinateFormatter extends ValueFormatterBase {

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof GlobeCoordinateValue)) {
			throw new InvalidArgumentException('DataValue is not a GlobeCoordinateValue.');
		}

		return new GeoJsonResourceNode(
			$this->toString($value),
			$this->toGeoJson($value)
		);
	}

	private function toString(GlobeCoordinateValue $value) {
		$options = new FormatterOptions();
		$options->setOption(ValueFormatter::OPT_LANG, $this->getOption(ValueFormatter::OPT_LANG));
		$formatter = new \DataValues\Geo\Formatters\GlobeCoordinateFormatter($options);

		return $formatter->format($value);
	}

	private function toGeoJson(GlobeCoordinateValue $value) {
		return new Point(array(
			$this->roundDegrees($value->getLatitude(), $value->getPrecision()),
			$this->roundDegrees($value->getLongitude(), $value->getPrecision())
		));
	}

	/**
	 * copy of GeoCoordinateFormatter::roundDegrees
	 */
	private function roundDegrees($degrees, $precision) {
		if($precision <= 0) {
			$precision = 1 / 3600;
		}

		$sign = $degrees > 0 ? 1 : -1;
		$reduced = round(abs($degrees) / $precision);
		$expanded = $reduced * $precision;

		return $sign * $expanded;
	}
}
