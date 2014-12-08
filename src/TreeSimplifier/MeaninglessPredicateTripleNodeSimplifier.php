<?php

namespace PPP\Wikidata\TreeSimplifier;

use InvalidArgumentException;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\ResourceNode;
use PPP\DataModel\TripleNode;
use PPP\DataModel\UnionNode;
use PPP\Module\TreeSimplifier\NodeSimplifier;

/**
 * Simplifies a triple node with an unuseful predicate like "name"
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class MeaninglessPredicateTripleNodeSimplifier implements NodeSimplifier {

	private static $MEANINGLESS_PREDICATES = array(
		'name'
	);

	/**
	 * @see NodeSimplifier::isSimplifierFor
	 */
	public function isSimplifierFor(AbstractNode $node) {
		return $node instanceof TripleNode &&
			$node->getPredicate() instanceof ResourceListNode &&
			$node->getObject() instanceof MissingNode;
	}

	/**
	 * @see NodeSimplifier::doSimplification
	 */
	public function simplify(AbstractNode $node) {
		if(!$this->isSimplifierFor($node)) {
			throw new InvalidArgumentException('MeaninglessPredicateTripleNodeSimplifier can only clean TripleNode objects');
		}

		return $this->doSimplification($node);
	}

	public function doSimplification(TripleNode $node) {
		$meaninglessPredicates = array();
		$otherPredicates = array();

		/** @var ResourceNode $predicate */
		foreach($node->getPredicate() as $predicate) {
			if(in_array($predicate->getValue(), self::$MEANINGLESS_PREDICATES)) {
				$meaninglessPredicates[] = $predicate;
			} else {
				$otherPredicates[] = $predicate;
			}
		}

		if(empty($meaninglessPredicates)) {
			return $node;
		} else if(empty($otherPredicates)) {
			return $node->getSubject();
		} else {
			return new UnionNode(array(
				$node->getSubject(),
				new TripleNode($node->getSubject(), new ResourceListNode($otherPredicates), $node->getObject())
			));
		}
	}
}
