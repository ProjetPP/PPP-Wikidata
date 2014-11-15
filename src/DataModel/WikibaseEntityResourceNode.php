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
	 * @var string
	 */
	private $description;

	/**
	 * @param string $value
	 * @param EntityId $entityId
	 * @param string $description
	 */
	public function __construct($value, EntityId $entityId, $description = '') {
		$this->entityId = $entityId;
		$this->description = $description;

		parent::__construct($value);
	}

	/**
	 * @return EntityId
	 */
	public function getEntityId() {
		return $this->entityId;
	}

	/**
	 * @return string Returns a string describing the entity.
	 *
	 * Example: For Jimmy Wales "Wikipedia co-founder"
	 */
	public function getDescription() {
		return $this->description;
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
