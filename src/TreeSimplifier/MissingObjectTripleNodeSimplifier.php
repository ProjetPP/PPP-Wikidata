<?php

namespace PPP\Wikidata\TreeSimplifier;

use OutOfBoundsException;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\ResourceNode;
use PPP\DataModel\TripleNode;
use PPP\Module\TreeSimplifier\AbstractTripleNodeSimplifier;
use PPP\Module\TreeSimplifier\NodeSimplifierFactory;
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
class MissingObjectTripleNodeSimplifier extends AbstractTripleNodeSimplifier {

	/**
	 * @var WikibaseEntityProvider
	 */
	private $entityProvider;

	/**
	 * @param NodeSimplifierFactory $simplifierFactory
	 * @param WikibaseEntityProvider $entityProvider
	 */
	public function __construct(NodeSimplifierFactory $simplifierFactory, WikibaseEntityProvider $entityProvider) {
		$this->entityProvider = $entityProvider;

		parent::__construct($simplifierFactory);
	}

	/**
	 * @see NodeSimplifier::isSimplifierFor
	 */
	public function isSimplifierFor(AbstractNode $node) {
		return $node instanceof TripleNode && $node->getObject() instanceof MissingNode;
	}

	/**
	 * @see AbstractTripleNodeSimplifier::doSimplification
	 * @param ResourceListNode $subjects
	 * @param ResourceListNode $predicates
	 * @param MissingNode $objects
	 */
	protected function doSimplification(AbstractNode $subjects, AbstractNode $predicates, AbstractNode $objects) {
		$snaks = array();

		foreach($subjects as $subject) {
			foreach($predicates as $predicate) {
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
