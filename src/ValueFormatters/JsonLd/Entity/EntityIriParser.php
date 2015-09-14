<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd\Entity;

use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\DataModel\Entity\EntityIdParsingException;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class EntityIriParser implements EntityIdParser {

	const IRI_PATTERN = '/^https?:\/\/www.wikidata.org\/entity\/(.*)$/';

	/**
	 * @var EntityIdParser
	 */
	private $entityIdParser;

	public function __construct(EntityIdParser $entityIdParser) {
		$this->entityIdParser = $entityIdParser;
	}

	/**
	 * @see EntityIdParser::parse
	 */
	public function parse($entityIri) {
		if(preg_match(self::IRI_PATTERN, $entityIri, $m)) {
			return $this->entityIdParser->parse($m[1]);
		} else {
			throw new EntityIdParsingException('Invalid entity IRI');
		}
	}
}
