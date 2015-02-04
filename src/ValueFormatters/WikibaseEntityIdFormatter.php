<?php

namespace PPP\Wikidata\ValueFormatters;

use InvalidArgumentException;
use OutOfBoundsException;
use PPP\DataModel\JsonLdResourceNode;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;
use ValueFormatters\ValueFormatterBase;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\FingerprintProvider;
use Wikibase\EntityStore\EntityStore;

/**
 * Returns the label of a given Wikibase entity id
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseEntityIdFormatter extends ValueFormatterBase implements DataValueFormatter {

	/**
	 * @var EntityStore
	 */
	private $entityStore;

	/**
	 * @var WikibaseEntityIdJsonLdFormatter
	 */
	private $entityJsonLdFormatter;

	/**
	 * @param EntityStore $entityStore
	 * @param WikibaseEntityIdJsonLdFormatter $entityJsonLdFormatter
	 * @param FormatterOptions $options
	 */
	public function __construct(
		EntityStore $entityStore,
		WikibaseEntityIdJsonLdFormatter $entityJsonLdFormatter,
		FormatterOptions $options
	) {
		$this->entityStore = $entityStore;
		$this->entityJsonLdFormatter = $entityJsonLdFormatter;

		parent::__construct($options);
	}

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof EntityIdValue)) {
			throw new InvalidArgumentException('$value should be a DataValue');
		}

		$entity = $this->entityStore->getEntityDocumentLookup()->getEntityDocumentForId($value->getEntityId());

		$stringAlternative = $entity->getId()->getSerialization();
		if($entity instanceof FingerprintProvider) {
			$stringAlternative = $this->getLabelFromFingerprint($entity->getFingerprint());
		}

		$jsonLd = $this->entityJsonLdFormatter->format($entity->getId());
		$jsonLd->{'@context'} = 'http://schema.org';

		return new JsonLdResourceNode(
			$stringAlternative,
			$jsonLd
		);
	}

	private function getLabelFromFingerprint(Fingerprint $fingerprint) {
		try {
			return $fingerprint->getLabel($this->getOption(ValueFormatter::OPT_LANG))->getText();
		} catch(OutOfBoundsException $e) {
			return '';
		}
	}
}
