<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd\Entity;

use InvalidArgumentException;
use OutOfBoundsException;
use stdClass;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;
use ValueFormatters\ValueFormatterBase;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Statement\StatementListProvider;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\FingerprintProvider;
use Wikibase\DataModel\Term\Term;

/**
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class ExtendedJsonLdEntityFormatter extends ValueFormatterBase {

	/**
	 * @var ValueFormatter
	 */
	private $entityFormatter;

	/**
	 * @var ValueFormatter
	 */
	private $snakFormatter;

	/**
	 * @param ValueFormatter $entityFormatter
	 * @param ValueFormatter $snakFormatter
	 * @param FormatterOptions $options
	 */
	public function __construct(ValueFormatter $entityFormatter, ValueFormatter $snakFormatter, FormatterOptions $options) {

		$this->entityFormatter = $entityFormatter;
		$this->snakFormatter = $snakFormatter;

		parent::__construct($options);
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
		$resource = $this->entityFormatter->format($entity);

		if($entity instanceof FingerprintProvider) {
			$this->addFingerprintAliasesToResource($entity->getFingerprint(), $resource);
		}

		if($entity instanceof StatementListProvider) {
			$this->addStatementListToResource($entity->getStatements(), $resource);
		}

		return $resource;
	}

	private function addFingerprintAliasesToResource(Fingerprint $fingerprint, stdClass $resource) {
		$languageCode = $this->getOption(ValueFormatter::OPT_LANG);

		try {
			$aliasGroup = $fingerprint->getAliasGroup($languageCode);
			$resource->alternateName = array();
			foreach($aliasGroup->getAliases() as $alias) {
				$resource->alternateName[] = $this->newResourceFromTerm(new Term($aliasGroup->getLanguageCode(), $alias));
			}
		} catch(OutOfBoundsException $e) {
			//Just ignore it
		}
	}

	private function addStatementListToResource(StatementList $statementList, stdClass $resource) {
		foreach($statementList->getBestStatements()->getMainSnaks() as $snak) {
			$formatted = $this->snakFormatter->format($snak);

			foreach($formatted as $property => $value) {
				if(isset($resource->{$property}) && !is_array($resource->{$property})) {
					continue; //We do not allow to edit special properties
				}
				$resource->{$property}[] = $value;
			}
		}
	}

	private function newResourceFromTerm(Term $term) {
		$literal = new stdClass();
		$literal->{'@language'} = $term->getLanguageCode();
		$literal->{'@value'} = $term->getText();
		return $literal;
	}
}
