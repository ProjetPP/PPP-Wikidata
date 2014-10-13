<?php

namespace PPP\Wikidata\SentenceTreeSimplifier;

use PPP\DataModel\AbstractNode;
use PPP\DataModel\TripleNode;

/**
 * Simplifies the sentence tree by doing requests.
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 *
 * @todo allow predicates to be triple node too?
 */
class SentenceTreeSimplifier {

	/**
	 * @var NodeSimplifier[]
	 */
	private $simplifiers;

	/**
	 * @param NodeSimplifier[] $simplifiers
	 */
	public function __construct(array $simplifiers = array()) {
		$this->simplifiers = $simplifiers;
	}

	/**
	 * @param AbstractNode $node
	 * @return AbstractNode
	 */
	public function simplify(AbstractNode $node) {
		$node = $this->recusiveSimplification($node);

		foreach($this->simplifiers as $simplifier) {
			if($simplifier->isSimplifierFor($node)) {
				$node = $simplifier->simplify($node);
			}
		}

		return $node;
	}

	private function recusiveSimplification(AbstractNode $node ) {
		if($node instanceof TripleNode) {
			return new TripleNode(
				$this->simplify($node->getSubject()),
				$node->getPredicate(),
				$this->simplify($node->getObject())
			);
		}

		return $node;
	}
}
