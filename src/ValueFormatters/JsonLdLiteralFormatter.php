<?php

namespace PPP\Wikidata\ValueFormatters;

use InvalidArgumentException;
use PPP\DataModel\JsonLdResourceNode;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdDataValueFormatter;
use PPP\Wikidata\WikibaseResourceNode;
use stdClass;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatterBase;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdLiteralFormatter extends ValueFormatterBase {

	/**
	 * @var JsonLdDataValueFormatter
	 */
	private $jsonLdFormatter;

	/**
	 * @param JsonLdDataValueFormatter $jsonLdFormatter
	 * @param FormatterOptions $options
	 */
	public function __construct(
		JsonLdDataValueFormatter $jsonLdFormatter,
		FormatterOptions $options
	) {
		$this->jsonLdFormatter = $jsonLdFormatter;

		parent::__construct($options);
	}

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof WikibaseResourceNode)) {
			throw new InvalidArgumentException('$value is not a WikibaseResourceNode.');
		}

		$literal = $this->jsonLdFormatter->format($value->getDataValue());

		return new JsonLdResourceNode(
			$literal->{'@value'},
			$this->buildResourceFromLiteral($literal)
		);
	}

	private function buildResourceFromLiteral(stdClass $literal) {
		$resource = new stdClass();
		$resource->{'@context'} = 'http://schema.org';
		$resource->{'http://www.w3.org/1999/02/22-rdf-syntax-ns#value'} = $literal;

		if(property_exists($literal, '@type')) {
			$resource->{'@type'} = $literal->{'@type'};
		} else {
			$resource->{'@type'} = 'Text';
		}

		return $resource;
	}
}
