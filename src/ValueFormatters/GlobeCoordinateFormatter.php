<?php

namespace PPP\Wikidata\ValueFormatters;

use InvalidArgumentException;
use PPP\DataModel\JsonLdResourceNode;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdDataValueFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdGlobeCoordinateFormatter;
use PPP\Wikidata\WikibaseResourceNode;
use stdClass;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatterBase;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 * @todo removes in favor of something more generic
 */
class GlobeCoordinateFormatter extends ValueFormatterBase implements WikibaseResourceNodeFormatter {

	/**
	 * @var JsonLdGlobeCoordinateFormatter
	 */
	private $jsonLdGlobeCoordinateFormatter;

	/**
	 * @var JsonLdDataValueFormatter
	 */
	private $entityJsonLdFormatter;

	/**
	 * @param JsonLdGlobeCoordinateFormatter $jsonLdGlobeCoordinateFormatter
	 * @param JsonLdDataValueFormatter $entityJsonLdFormatter
	 * @param FormatterOptions $options
	 */
	public function __construct(
		JsonLdGlobeCoordinateFormatter $jsonLdGlobeCoordinateFormatter,
		JsonLdDataValueFormatter $entityJsonLdFormatter,
		FormatterOptions $options
	) {
		$this->jsonLdGlobeCoordinateFormatter = $jsonLdGlobeCoordinateFormatter;
		$this->entityJsonLdFormatter = $entityJsonLdFormatter;

		parent::__construct($options);
	}

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof WikibaseResourceNode)) {
			throw new InvalidArgumentException('$value is not a WikibaseResourceNode.');
		}

		$resource = $this->jsonLdGlobeCoordinateFormatter->format($value->getDataValue());
		$resource->{'@context'} = 'http://schema.org';

		$fromSubject = $value->getFromSubject();
		if($fromSubject !== null) {
			$resource->{'@reverse'} = new stdClass();
			$resource->{'@reverse'}->geo = $this->entityJsonLdFormatter->format($fromSubject);
		}

		return new JsonLdResourceNode(
			$resource->name,
			$resource
		);
	}
}
