<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\Geo\Values\GlobeCoordinateValue;
use InvalidArgumentException;
use PPP\DataModel\JsonLdResourceNode;
use stdClass;
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

		return new JsonLdResourceNode(
			$this->toString($value),
			$this->toJsonLd($value)
		);
	}

	private function toString(GlobeCoordinateValue $value) {
		$options = new FormatterOptions();
		$options->setOption(ValueFormatter::OPT_LANG, $this->getOption(ValueFormatter::OPT_LANG));
		$formatter = new \DataValues\Geo\Formatters\GlobeCoordinateFormatter($options);

		return $formatter->format($value);
	}

	/**
	 * @param GlobeCoordinateValue $value
	 */
	private function toJsonLd(GlobeCoordinateValue $value) {
		$resource = new stdClass();
		$resource->{'@context'} = 'http://schema.org';
		$resource->{'@type'} = 'GeoCoordinates';
		$resource->latitude = $this->roundDegrees($value->getLatitude(), $value->getPrecision());
		$resource->longitude = $this->roundDegrees($value->getLongitude(), $value->getPrecision());

		return $resource;
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
