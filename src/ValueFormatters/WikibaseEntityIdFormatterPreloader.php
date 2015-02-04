<?php

namespace PPP\Wikidata\ValueFormatters;

use OutOfBoundsException;
use PPP\DataModel\ResourceListNode;
use PPP\Wikidata\WikibaseResourceNode;
use PPP\Wikidata\Wikipedia\PerSiteLinkProvider;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\EntityStore\EntityStore;

/**
 * Preload data for formatting of groups of EntityIdValue
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseEntityIdFormatterPreloader {

	/**
	 * @var EntityStore
	 */
	private $entityStore;

	/**
	 * @var PerSiteLinkProvider[]
	 */
	private $perSiteLinkProviders;

	/**
	 * @var string
	 */
	private $languageCode;

	/**
	 * @param EntityStore $entityStore
	 * @param PerSiteLinkProvider[] $perSiteLinkProviders
	 * @param $languageCode
	 */
	public function __construct(EntityStore $entityStore, array $perSiteLinkProviders, $languageCode) {
		$this->entityStore = $entityStore;
		$this->perSiteLinkProviders = $perSiteLinkProviders;
		$this->languageCode = $languageCode;
	}

	public function preload(ResourceListNode $resourceList) {
		$entityIds = $this->findEntityIds($resourceList);

		$siteLinks = $this->findSiteLinksFromEntityIds($entityIds);
		foreach($this->perSiteLinkProviders as $provider) {
			$provider->loadFromSiteLinks($siteLinks);
		}
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

	private function findSiteLinksFromEntityIds(array $entityIds) {
		$siteLinks = array();

		$entities = $this->entityStore->getEntityDocumentLookup()->getEntityDocumentsForIds($entityIds);
		foreach($entities as $entity) {
			if($entity instanceof Item) {
				try {
					$siteLinks[] = $entity->getSiteLinkList()->getBySiteId($this->languageCode . 'wiki');
				} catch(OutOfBoundsException $e) {
				}
			}
		}

		return $siteLinks;
	}
}
