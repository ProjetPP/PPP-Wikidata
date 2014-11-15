<?php

namespace PPP\Wikidata\DataModel\Serializers;

use PPP\DataModel\ResourceNode;
use PPP\DataModel\Serializers\BasicResourceNodeSerializer;
use PPP\Wikidata\DataModel\WikibaseEntityResourceNode;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseEntityResourceNodeSerializer extends BasicResourceNodeSerializer {

	public function __construct() {
		parent::__construct('wikibase-entity');
	}

	/**
	 * @see AbstractResourceNodeSerializer::getAdditionalSerialization
	 * @param WikibaseEntityResourceNode $node
	 */
	protected function getAdditionalSerialization(ResourceNode $node) {
		return array(
			'entity-id' => $node->getEntityId()->getSerialization()
		);
	}
}
