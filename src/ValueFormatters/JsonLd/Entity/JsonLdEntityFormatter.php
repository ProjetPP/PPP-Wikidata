<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd\Entity;

use InvalidArgumentException;
use OutOfBoundsException;
use stdClass;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;
use ValueFormatters\ValueFormatterBase;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\FingerprintProvider;
use Wikibase\DataModel\Term\Term;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdEntityFormatter extends ValueFormatterBase {

	/**
	 * Base URI for Wikibase entities. For Wikidata it is "http://www.wikidata.org/entity/"
	 */
	const OPT_ENTITY_BASE_URI = 'entity-baseuri';

	/**
	 * @param FormatterOptions $options
	 */
	public function __construct(FormatterOptions $options) {
		parent::__construct($options);

		$this->requireOption(self::OPT_ENTITY_BASE_URI);
	}

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof EntityDocument)) {
			throw new InvalidArgumentException('$value is not an EntityDocument.');
		}

		return $this->toJsonLd($value);
	}

	private function toJsonLd(EntityDocument $entity) {
		$resource = new stdClass();
		$resource->{'@type'} = 'Thing';
		$resource->name = $entity->getId()->getSerialization();
		$resource->{'@id'} = $this->getOption(JsonLdEntityFormatter::OPT_ENTITY_BASE_URI) . $entity->getId()->getSerialization();

		if($entity instanceof FingerprintProvider) {
			$this->addFingerprintToResource($entity->getFingerprint(), $resource);
		}

		return $resource;
	}

	private function addFingerprintToResource(Fingerprint $fingerprint, stdClass $resource) {
		$languageCode = $this->getOption(ValueFormatter::OPT_LANG);

		try {
			$resource->name = $this->newResourceFromTerm($fingerprint->getLabel($languageCode));
		} catch(OutOfBoundsException $e) {
			//Just ignore it
		}

		try {
			$resource->description = $this->newResourceFromTerm($fingerprint->getDescription($languageCode));
		} catch(OutOfBoundsException $e) {
			//Just ignore it
		}
	}

	private function newResourceFromTerm(Term $term) {
		$literal = new stdClass();
		$literal->{'@language'} = $term->getLanguageCode();
		$literal->{'@value'} = $term->getText();
		return $literal;
	}
}
