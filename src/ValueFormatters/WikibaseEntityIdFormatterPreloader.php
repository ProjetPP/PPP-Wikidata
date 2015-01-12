<?php

namespace PPP\Wikidata\ValueFormatters;

use PPP\DataModel\ResourceListNode;
use PPP\Wikidata\WikibaseEntityProvider;
use PPP\Wikidata\WikibaseResourceNode;
use Wikibase\DataModel\Entity\EntityIdValue;

/**
 * Preload data for formatting of groups of EntityIdValue
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseEntityIdFormatterPreloader {

	/**
	 * @var WikibaseEntityProvider
	 */
	private $entityProvider;

	/**
	 * @param WikibaseEntityProvider $entityProvider
	 */
	public function __construct(WikibaseEntityProvider $entityProvider) {
		$this->entityProvider = $entityProvider;
	}

	public function preload(ResourceListNode $resourceList) {
		$entityIds = $this->findEntityIds($resourceList);

		$this->entityProvider->loadEntities($entityIds);
	}

	private function findEntityIds(ResourceListNode $resourceList) {
		$entityIds = array();

		foreach($resourceList as $resource) {
			if($resource instanceof WikibaseResourceNode && $resource->getDataValue() instanceof EntityIdValue) {
				$entityIds[] = $resource->getDataValue()->getEntityId();
			}
		}

		return $entityIds;
	}
}
