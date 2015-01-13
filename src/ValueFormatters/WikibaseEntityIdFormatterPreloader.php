<?php

namespace PPP\Wikidata\ValueFormatters;

use OutOfBoundsException;
use PPP\DataModel\ResourceListNode;
use PPP\Wikidata\WikibaseEntityProvider;
use PPP\Wikidata\WikibaseResourceNode;
use PPP\Wikidata\Wikipedia\PerSiteLinkProvider;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;

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
	 * @var PerSiteLinkProvider[]
	 */
	private $perSiteLinkProviders;

	/**
	 * @var string
	 */
	private $languageCode;

	/**
	 * @param WikibaseEntityProvider $entityProvider
	 * @param PerSiteLinkProvider[] $perSiteLinkProviders
	 * @param $languageCode
	 */
	public function __construct(WikibaseEntityProvider $entityProvider, array $perSiteLinkProviders, $languageCode) {
		$this->entityProvider = $entityProvider;
		$this->perSiteLinkProviders = $perSiteLinkProviders;
		$this->languageCode = $languageCode;
	}

	public function preload(ResourceListNode $resourceList) {
		$entityIds = $this->findEntityIds($resourceList);

		$this->entityProvider->loadEntities($entityIds);

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

		foreach($entityIds as $entityId) {
			if($entityId instanceof ItemId) {
				$item = $this->entityProvider->getItem($entityId);

				try{
					$siteLinks[] = $item->getSiteLinkList()->getBySiteId($this->languageCode . 'wiki');
				} catch(OutOfBoundsException $e) {
				}
			}
		}

		return $siteLinks;
	}
}
