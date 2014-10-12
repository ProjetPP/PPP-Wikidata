<?php

namespace PPP\Wikidata\SentenceTreeSimplifier;

use InvalidArgumentException;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\TripleNode;
use PPP\Wikidata\WikibaseResourceNode;
use Wikibase\Api\Service\RevisionGetter;
use Wikibase\DataModel\Claim\Claims;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertySomeValueSnak;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Snak\Snak;

/**
 * Simplifies a triple node when the object is missing.
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class MissingObjectTripleNodeSimplifier implements NodeSimplifier {

	/**
	 * @var RevisionGetter
	 */
	private $revisionGetter;

	/**
	 * @param RevisionGetter $revisionGetter
	 */
	public function __construct(RevisionGetter $revisionGetter) {
		$this->revisionGetter = $revisionGetter;
	}

	/**
	 * @see AbstractNode::isSimplifierFor
	 */
	public function isSimplifierFor(AbstractNode $node) {
		return $node instanceof TripleNode &&
			$node->getSubject() instanceof WikibaseResourceNode &&
			$node->getPredicate() instanceof WikibaseResourceNode &&
			$node->getObject() instanceof MissingNode;
	}

	/**
	 * @see AbstractNode::simplify
	 */
	public function simplify(AbstractNode $node) {
		/** @var TripleNode $node */
		if(!$this->isSimplifierFor($node)) {
			throw new InvalidArgumentException('MissingObjectTripleNodeSimplifier can not simplify this node!');
		}

		/** @var ItemId $itemId */
		$itemId = $node->getSubject()->getDataValue()->getEntityId();
		/** @var PropertyId $propertyId */
		$propertyId = $node->getPredicate()->getDataValue()->getEntityId();

		$itemRevision = $this->revisionGetter->getFromId($itemId);
		if($itemRevision === false) {
			throw new SimplifierException('The item ' . $itemId->getSerialization() . ' does not exists');
		}

		/** @var Item $item */
		$item = $itemRevision->getContent()->getNativeData();
		return $this->snakToNode($this->getSnakForProperty($item, $propertyId));
	}


	/**
	 * @return Snak
	 */
	private function getSnakForProperty(Item $item, PropertyId $propertyId) {
		$claims = new Claims($item->getClaims());

		foreach($claims->getClaimsForProperty($propertyId)->getBestClaims() as $claim) {
			return $claim->getMainSnak();
		}

		throw new SimplifierException(
			'No value found for property ' . $propertyId->getSerialization() . ' in item ' . $item->getId()->getSerialization()
		);
	}

	/**
	 * @return AbstractNode
	 */
	private function snakToNode(Snak $snak) {
		if($snak instanceof PropertyValueSnak) {
			return new WikibaseResourceNode('', $snak->getDataValue()); //TODO add serialization?
		} else if($snak instanceof PropertySomeValueSnak) {
			return new MissingNode();
		}
		//TODO case of PropertyNoValueSnak (return the negation of the triple?)

		throw new SimplifierException('Unknown Snak type: ' . $snak->getType());
	}
}
