<?php

namespace PPP\Wikidata;

use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceNode;
use PPP\DataModel\TripleNode;

/**
 * Clean trees in order to fit Wikidata module needs
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikidataTreeCleaner {

	private static $MEANINGLESS_PREDICATES = array(
		'name'
	);

	public function clean(AbstractNode $node) {
		if(!($node instanceof TripleNode)) {
			return $node;
		}

		$node = $this->removeMeaninglessPredicates($node);

		return $node;
	}

	private function removeMeaninglessPredicates(TripleNode $node) {
		$predicate = $node->getPredicate();
		if(
			$predicate instanceof ResourceNode &&
			in_array($predicate->getValue(), self::$MEANINGLESS_PREDICATES) &&
			$node->getObject()->equals(new MissingNode())
		) {
			return $node->getSubject();
		}
		return $node;
	}
}
