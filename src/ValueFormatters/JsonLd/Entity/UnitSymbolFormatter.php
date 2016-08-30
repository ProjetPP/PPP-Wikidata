<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd\Entity;

use OutOfBoundsException;
use ValueFormatters\FormatterOptions;
use ValueFormatters\FormattingException;
use ValueFormatters\ValueFormatter;
use ValueFormatters\ValueFormatterBase;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\DataModel\Entity\EntityIdParsingException;
use Wikibase\DataModel\Services\Lookup\ItemLookup;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class UnitSymbolFormatter extends ValueFormatterBase {

	/**
	 * @var EntityIdParser
	 */
	private $entityIriParser;

	/**
	 * @var EntityOntology
	 */
	private $entityOntology;

	/**
	 * @var ItemLookup
	 */
	private $itemLookup;

	public function __construct(
		EntityIdParser $entityIriParser,
		EntityOntology $entityOntology,
		ItemLookup $itemLookup,
		FormatterOptions $options = null
	) {
		$this->entityIriParser = $entityIriParser;
		$this->entityOntology = $entityOntology;
		$this->itemLookup = $itemLookup;

		parent::__construct($options);
	}

	/**
	 * @see ValueFormatterBase::format
	 */
	public function format($value) {
		try {
			$itemId = $this->entityIriParser->parse($value);
		} catch(EntityIdParsingException $e) {
			throw new FormattingException('Invalid unit IRI: ' . $value);
		}

		$item = $this->itemLookup->getItemForId($itemId);
		if($item === null) {
			throw new FormattingException('Item not found: ' . $value);
		}

		try {
			return $this->entityOntology->getUnitSymbol($item);
		} catch(OutOfBoundsException $e) {
			try {
				return $item->getFingerprint()->getLabel($this->getOption(ValueFormatter::OPT_LANG))->getText();
			} catch (OutOfBoundsException $e) {
				throw new FormattingException('No unit symbol and no label for IRI ' . $value);
			}
		}
	}
}
