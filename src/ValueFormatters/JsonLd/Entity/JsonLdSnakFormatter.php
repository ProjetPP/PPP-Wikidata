<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd\Entity;

use InvalidArgumentException;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdDataValueFormatter;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatterBase;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\PropertyLookup;
use Wikibase\DataModel\Entity\PropertyNotFoundException;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Snak\Snak;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdSnakFormatter extends ValueFormatterBase {

	/**
	 * Array of allowed vocabularies to map snaks to.
	 * Example: array('http://schema.org/')
	 */
	const OPT_ALLOWED_VOCABULARIES = 'allowed-vocabularies';

	/**
	 * @var PropertyLookup
	 */
	private $propertyLookup;

	/**
	 * @var EntityOntology
	 */
	private $entityOntology;

	/**
	 * @var JsonLdDataValueFormatter
	 */
	private $dataValueFormatter;

	/**
	 * @param PropertyLookup $propertyLookup
	 * @param EntityOntology $entityOntology
	 * @param JsonLdDataValueFormatter $dataValueFormatter
	 * @param FormatterOptions $options
	 */
	public function __construct(
		PropertyLookup $propertyLookup,
		EntityOntology $entityOntology,
		JsonLdDataValueFormatter $dataValueFormatter,
		FormatterOptions $options
	) {
		$this->propertyLookup = $propertyLookup;
		$this->entityOntology = $entityOntology;
		$this->dataValueFormatter = $dataValueFormatter;

		parent::__construct($options);

		$this->requireOption(self::OPT_ALLOWED_VOCABULARIES);
	}

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof Snak)) {
			throw new InvalidArgumentException('$value is not a Snak.');
		}

		return $this->toJsonLdProperties($value);
	}

	private function toJsonLdProperties(Snak $snak) {
		if(!($snak instanceof PropertyValueSnak)) {
			return array();
		}

		return array_fill_keys(
			$this->normalizePropertyIris($this->getPropertyIris($snak->getPropertyId())),
			$this->dataValueFormatter->format($snak->getDataValue())
		);
	}

	private function getPropertyIris(PropertyId $propertyId) {
		try {
			$property = $this->propertyLookup->getPropertyForId($propertyId);
		} catch(PropertyNotFoundException $e) {
			return array();
		}

		return $this->filterIris($this->entityOntology->getEquivalentPropertiesIris($property));
	}

	private function filterIris(array $iris) {
		$regex = $this->buildIriFilterRegex();
		return array_filter(
			$iris,
			function($iri) use ($regex) {
				return preg_match($regex, $iri);
			}
		);
	}

	private function buildIriFilterRegex() {
		$escaped = array_map(
			function($prefix) {
				return '(' . preg_quote($prefix, '/') . ')';
			},
			$this->getOption(self::OPT_ALLOWED_VOCABULARIES)
		);

		return '/^' . implode( '|', $escaped ) . '/';
	}

	private function normalizePropertyIris($iris) {
		return array_map(
			function($iri) {
				return str_replace('http://schema.org/', '', $iri);
			},
			$iris
		);
	}
}
