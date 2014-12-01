<?php

namespace PPP\Wikidata;

use InvalidArgumentException;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\ResourceNode;
use PPP\DataModel\TripleNode;
use PPP\Module\TreeSimplifier\NodeSimplifier;

/**
 * Clean triples in order to fit Wikidata module needs
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 * @todo if meaningless and non meaningless predicates are merged, keep the non meaningless
 */
class WikidataTripleNodeCleaner implements NodeSimplifier {

	private static $MEANINGLESS_PREDICATES = array(
		'name'
	);

	/**
	 * @see NodeSimplifier::isSimplifierFor
	 */
	public function isSimplifierFor(AbstractNode $node) {
		return $node instanceof TripleNode;
	}

	/**
	 * @see NodeSimplifier::doSimplification
	 */
	public function simplify(AbstractNode $node) {
		if(!$this->isSimplifierFor($node)) {
			throw new InvalidArgumentException('WikidataTripleNodeCleaner can only clean TripleNode objects');
		}

		return $this->doSimplification($node);
	}

	private function doSimplification(TripleNode $node) {
		if($node->getPredicate() instanceof ResourceListNode && $node->getObject()->equals(new MissingNode())) {
			return $this->removeMeaninglessPredicates($node);
		}

		return $node;
	}

	public function clean(AbstractNode $node) {
		if(!($node instanceof TripleNode)) {
			return $node;
		}

		$node = $this->removeMeaninglessPredicates($node);

		return $node;
	}

	private function removeMeaninglessPredicates(TripleNode $node) {
		$withMeaninglessPredicate = false;

		/** @var ResourceNode $predicate */
		foreach($node->getPredicate() as $predicate) {
			if(in_array($predicate->getValue(), self::$MEANINGLESS_PREDICATES)) {
				$withMeaninglessPredicate = true;
			}
		}

		if($withMeaninglessPredicate) {
			return $node->getSubject();
		} else {
			return $node;
		}
	}
}
