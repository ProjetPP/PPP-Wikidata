<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\Geo\Values\GlobeCoordinateValue;
use InvalidArgumentException;
use PPP\DataModel\JsonLdResourceNode;
use PPP\Wikidata\WikibaseResourceNode;
use stdClass;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;
use ValueFormatters\ValueFormatterBase;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 * @todo support globes
 */
class GlobeCoordinateFormatter extends ValueFormatterBase implements WikibaseResourceNodeFormatter {

	/**
	 * @var WikibaseEntityIdJsonLdFormatter
	 */
	private $entityJsonLdFormatter;

	/**
	 * @param WikibaseEntityIdJsonLdFormatter $entityJsonLdFormatter
	 * @param FormatterOptions $options
	 */
	public function __construct(
		WikibaseEntityIdJsonLdFormatter $entityJsonLdFormatter,
		FormatterOptions $options
	) {
		$this->entityJsonLdFormatter = $entityJsonLdFormatter;

		parent::__construct($options);
	}

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof WikibaseResourceNode && $value->getDataValue() instanceof GlobeCoordinateValue)) {
			throw new InvalidArgumentException('$value is not a GlobeCoordinateValue.');
		}

		return new JsonLdResourceNode(
			$this->toString($value->getDataValue()),
			$this->toJsonLd($value)
		);
	}

	private function toString(GlobeCoordinateValue $value) {
		$options = new FormatterOptions();
		$options->setOption(ValueFormatter::OPT_LANG, $this->getOption(ValueFormatter::OPT_LANG));
		$formatter = new \DataValues\Geo\Formatters\GlobeCoordinateFormatter($options);

		return $formatter->format($value);
	}

	private function toJsonLd(WikibaseResourceNode $node) {
		$value = $node->getDataValue();

		$resource = new stdClass();
		$resource->{'@context'} = 'http://schema.org';
		$resource->{'@type'} = 'GeoCoordinates';
		$resource->latitude = $this->roundDegrees($value->getLatitude(), $value->getPrecision());
		$resource->longitude = $this->roundDegrees($value->getLongitude(), $value->getPrecision());

		$fromSubject = $node->getFromSubject();
		if($fromSubject !== null) {
			$resource->{'@reverse'} = new stdClass();
			$resource->{'@reverse'}->geo = $this->entityJsonLdFormatter->format($fromSubject);
		}

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
