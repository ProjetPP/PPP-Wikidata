<?php

namespace PPP\Wikidata\TreeSimplifier;

use InvalidArgumentException;
use OutOfBoundsException;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\TripleNode;
use PPP\Module\TreeSimplifier\NodeSimplifier;
use PPP\Wikidata\WikibaseEntityProvider;
use PPP\Wikidata\WikibaseResourceNode;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Snak\Snak;
use Wikibase\DataModel\Statement\BestStatementsFinder;
use Wikibase\DataModel\Statement\Statement;

/**
 * Simplifies a triple node when the object is missing.
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class MissingObjectTripleNodeSimplifier implements NodeSimplifier {

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

	/**
	 * @see NodeSimplifier::isSimplifierFor
	 */
	public function isSimplifierFor(AbstractNode $node) {
		return $node instanceof TripleNode &&
			$node->getSubject() instanceof ResourceListNode &&
			$node->getPredicate() instanceof ResourceListNode &&
			$node->getObject() instanceof MissingNode;
	}

	/**
	 * @see NodeSimplifier::doSimplification
	 */
	public function simplify(AbstractNode $node) {
		if(!$this->isSimplifierFor($node)) {
			throw new InvalidArgumentException('MissingObjectTripleNodeSimplifier can only simplify TripleNode with a missing object');
		}

		return $this->doSimplification($node);
	}

	private function doSimplification(TripleNode $node) {
		$snaks = array();

		foreach($node->getSubject() as $subject) {
			foreach($node->getPredicate() as $predicate) {
				$snaks = array_merge(
					$snaks,
					$this->getSnaksForObject($subject, $predicate)
				);
			}
		}

		return $this->snaksToNode($snaks);
	}

	protected function getSnaksForObject(WikibaseResourceNode $subject, WikibaseResourceNode $predicate) {
		/** @var ItemId $itemId */
		$itemId = $subject->getDataValue()->getEntityId();
		/** @var PropertyId $propertyId */
		$propertyId = $predicate->getDataValue()->getEntityId();

		$item = $this->entityProvider->getItem($itemId);
		return $this->getSnaksForProperty($item, $propertyId);
	}


	/**
	 * @return Snak[]
	 */
	private function getSnaksForProperty(Item $item, PropertyId $propertyId) {
		$statementFinder = new BestStatementsFinder($item->getStatements());

		$snaks = array();
		$statements = array();
		try {
			$statements = $statementFinder->getBestStatementsForProperty($propertyId);
		} catch(OutOfBoundsException $e) {
			return array();
		}

		/** @var Statement $statement */
		foreach($statements as $statement) {
			$snaks[] = $statement->getMainSnak();
		}

		return $snaks;
	}

	private function snaksToNode(array $snaks) {
		$nodes = array();

		foreach($snaks as $snak) {
			if($snak instanceof PropertyValueSnak) {
				$nodes[] = new WikibaseResourceNode('', $snak->getDataValue());
			}
			//TODO case of PropertySomeValueSnak (MissingNode) and PropertyNoValueSnak (return the negation of the triple?)
		}

		return new ResourceListNode($nodes);
	}
}
