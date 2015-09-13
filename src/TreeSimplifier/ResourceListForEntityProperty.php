<?php

namespace PPP\Wikidata\TreeSimplifier;

use PPP\DataModel\ResourceListNode;
use PPP\Wikidata\WikibaseResourceNode;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\StatementListProvider;
use Wikibase\EntityStore\EntityNotFoundException;
use Wikibase\EntityStore\EntityStore;

/**
 * Simplifies a triple node when the object is missing.
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class ResourceListForEntityProperty {

	/**
	 * @var EntityStore
	 */
	private $entityStore;

	/**
	 * @param EntityStore $entityStore
	 */
	public function __construct(EntityStore $entityStore) {
		$this->entityStore = $entityStore;
	}

	/**
	 * @param EntityId $entityId
	 * @param PropertyId $propertyId
	 * @return ResourceListNode
	 */
	public function getForEntityProperty(EntityId $entityId, PropertyId $propertyId) {
		try {
			$entity = $this->entityStore->getEntityDocumentLookup()->getEntityDocumentForId($entityId);
		} catch(EntityNotFoundException $e) {
			return array();
		}

		$snaks = $this->getSnaksForProperty($entity, $propertyId);
		return new ResourceListNode($this->snaksToNodes($snaks, $entityId, $propertyId));
	}

	private function getSnaksForProperty(EntityDocument $entity, PropertyId $propertyId) {
		if(!$entity instanceof StatementListProvider) {
			return array();
		}

		return $entity->getStatements()->getByPropertyId($propertyId)->getBestStatements()->getMainSnaks();
	}

	private function snaksToNodes(array $snaks, EntityId $fromSubject, PropertyId $fromProperty) {
		$nodes = array();

		foreach($snaks as $snak) {
			if($snak instanceof PropertyValueSnak) {
				$nodes[] = new WikibaseResourceNode('', $snak->getDataValue(), $fromSubject, $fromProperty);
			}
			//TODO case of PropertySomeValueSnak (MissingNode) and PropertyNoValueSnak (return the negation of the triple?)
		}

		return $nodes;
	}
}
