<?php

namespace PPP\Wikidata\DataModel\Deserializers;

use PPP\DataModel\Deserializers\AbstractResourceNodeDeserializer;
use PPP\Wikidata\DataModel\WikibaseEntityResourceNode;
use Wikibase\DataModel\Entity\EntityIdParser;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseEntityResourceNodeDeserializer extends AbstractResourceNodeDeserializer {

	/**
	 * @var EntityIdParser
	 */
	private $entityIdParser;

	public function __construct(EntityIdParser $entityIdParser) {
		$this->entityIdParser = $entityIdParser;

		parent::__construct('wikibase-entity');
	}

	/**
	 * @see DispatchableDeserializer::getDeserialization
	 */
	protected function getDeserialization($value, array $serialization) {
		$this->requireAttribute($serialization, 'entity-id');

		return new WikibaseEntityResourceNode(
			$value,
			$this->entityIdParser->parse($serialization['entity-id'])
		);
	}
}

