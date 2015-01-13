<?php

namespace PPP\Wikidata\ValueFormatters;

use InvalidArgumentException;
use OutOfBoundsException;
use PPP\DataModel\JsonLdResourceNode;
use PPP\Wikidata\DataModel\WikibaseEntityResourceNode;
use PPP\Wikidata\WikibaseEntityProvider;
use stdClass;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;
use ValueFormatters\ValueFormatterBase;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\Term;

/**
 * Returns the label of a given Wikibase entity id
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseEntityIdFormatter extends ValueFormatterBase {

	/**
	 * @var WikibaseEntityProvider
	 */
	private $entityProvider;

	/**
	 * @param WikibaseEntityProvider $entityProvider
	 * @param FormatterOptions $options
	 */
	public function __construct(WikibaseEntityProvider $entityProvider, FormatterOptions $options) {
		$this->entityProvider = $entityProvider;
		parent::__construct($options);
	}

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof EntityIdValue)) {
			throw new InvalidArgumentException('$value should be a DataValue');
		}

		$fingerprint = $this->getFingerprintForEntityId($value->getEntityId());

		$resource = new stdClass();
		$resource->{'@context'} = 'http://schema.org';
		$resource->{'@type'} = 'Thing';
		$resource->{'@id'} = 'http://www.wikidata.org/entity/' . $value->getEntityId()->getSerialization();
		$this->addFingerprintToResource($fingerprint, $resource );

		return new JsonLdResourceNode(
			$this->getLabelFromFingerprint($fingerprint),
			$resource
		);
	}

	/**
	 * @return Fingerprint
	 */
	private function getFingerprintForEntityId(EntityId $entityId) {
		if($entityId instanceof ItemId) {
			return $this->entityProvider->getItem($entityId)->getFingerprint();
		} elseif($entityId instanceof PropertyId) {
			return $this->entityProvider->getProperty($entityId)->getFingerprint();
		} else {
			throw new InvalidArgumentException('Unknown entity type:' .  $entityId->getEntityType());
		}
	}

	private function getLabelFromFingerprint(Fingerprint $fingerprint) {
		try {
			return $fingerprint->getLabel($this->getOption(ValueFormatter::OPT_LANG))->getText();
		} catch(OutOfBoundsException $e) {
			return '';
		}
	}

	private function addFingerprintToResource(Fingerprint $fingerprint, stdClass $resource) {
		$languageCode = $this->getOption(ValueFormatter::OPT_LANG);

		try {
			$resource->name = $this->newResourceFromTerm($fingerprint->getLabel($languageCode));
		} catch(OutOfBoundsException $e) {
		}

		try {
			$resource->description = $this->newResourceFromTerm($fingerprint->getDescription($languageCode));
		} catch(OutOfBoundsException $e) {
		}

		try {
			$aliasGroup = $fingerprint->getAliasGroup($languageCode);
			$resource->alternateName = array();
			foreach($aliasGroup->getAliases() as $alias) {
				$resource->alternateName[] = $this->newResourceFromTerm(new Term($aliasGroup->getLanguageCode(), $alias));
			}
		} catch(OutOfBoundsException $e) {
		}
	}

	private function newResourceFromTerm(Term $term) {
		$resource = new stdClass();
		$resource->{'@language'} = $term->getLanguageCode();
		$resource->{'@value'} = $term->getText();
		return $resource;
	}
}
