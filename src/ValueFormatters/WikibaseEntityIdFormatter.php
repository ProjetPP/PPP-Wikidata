<?php

namespace PPP\Wikidata\ValueFormatters;

use InvalidArgumentException;
use OutOfBoundsException;
use PPP\DataModel\JsonLdResourceNode;
use PPP\Wikidata\WikibaseEntityProvider;
use PPP\Wikidata\Wikipedia\MediawikiArticleHeaderProvider;
use PPP\Wikidata\Wikipedia\MediawikiArticleImageProvider;
use stdClass;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;
use ValueFormatters\ValueFormatterBase;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\SiteLinkList;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\FingerprintProvider;
use Wikibase\DataModel\Term\Term;

/**
 * Returns the label of a given Wikibase entity id
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseEntityIdFormatter extends ValueFormatterBase implements DataValueFormatter {

	/**
	 * @var WikibaseEntityProvider
	 */
	private $entityProvider;

	/**
	 * @var WikibaseEntityJsonLdFormatter
	 */
	private $entityJsonLdFormatter;

	/**
	 * @param WikibaseEntityProvider $entityProvider
	 * @param WikibaseEntityJsonLdFormatter $entityJsonLdFormatter
	 * @param FormatterOptions $options
	 */
	public function __construct(
		WikibaseEntityProvider $entityProvider,
		WikibaseEntityJsonLdFormatter $entityJsonLdFormatter,
		FormatterOptions $options
	) {
		$this->entityProvider = $entityProvider;
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

		$entity = $this->entityProvider->getEntityDocument($value->getEntityId());

		$stringAlternative = $entity->getId()->getSerialization();
		if($entity instanceof FingerprintProvider) {
			$stringAlternative = $this->getLabelFromFingerprint($entity->getFingerprint());
		}

		return new JsonLdResourceNode(
			$stringAlternative,
			$this->entityJsonLdFormatter->format($entity)
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
