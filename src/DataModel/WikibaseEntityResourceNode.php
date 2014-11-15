<?php

namespace PPP\Wikidata\DataModel;

use PPP\DataModel\ResourceNode;
use Wikibase\DataModel\Entity\EntityId;

/**
 * A Wikibase entity resource.
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseEntityResourceNode extends ResourceNode {

	/**
	 * @var EntityId
	 */
	private $entityId;

	/**
	 * @param string $value
	 * @param EntityId $entityId
	 */
	public function __construct($value, EntityId $entityId) {
		$this->entityId = $entityId;

		parent::__construct($value);
	}

	/**
	 * @return EntityId
	 */
	public function getEntityId() {
		return $this->entityId;
	}

	/**
	 * @return string
	 */
	public function getValueType() {
		return 'wikibase-entity';
	}

	/**
	 * @see AbstractNode::equals
	 */
	public function equals($target) {
		return $target instanceof self &&
			$this->entityId->equals($target->entityId);
	}
}
