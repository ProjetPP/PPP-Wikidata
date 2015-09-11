<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd;

use DataValues\Geo\Formatters\GlobeCoordinateFormatter;
use DataValues\Geo\Values\GlobeCoordinateValue;
use InvalidArgumentException;
use stdClass;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatterBase;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 * @todo support globes
 */
class JsonLdGlobeCoordinateFormatter extends ValueFormatterBase implements JsonLdDataValueFormatter {

	/**
	 * @var GlobeCoordinateFormatter
	 */
	private $globeCoordinateFormatter;

	/**
	 * @param GlobeCoordinateFormatter $globeCoordinateFormatter
	 * @param FormatterOptions|null $options
	 */
	public function __construct(GlobeCoordinateFormatter $globeCoordinateFormatter, FormatterOptions $options = null) {
		$this->globeCoordinateFormatter = $globeCoordinateFormatter;

		parent::__construct($options);
	}

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof GlobeCoordinateValue)) {
			throw new InvalidArgumentException('$value is not a GlobeCoordinateValue.');
		}

		return $this->toJsonLd($value);
	}

	private function toJsonLd(GlobeCoordinateValue $value) {
		$resource = new stdClass();
		$resource->{'@type'} = 'GeoCoordinates';
		$resource->name = $this->globeCoordinateFormatter->format($value);
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
