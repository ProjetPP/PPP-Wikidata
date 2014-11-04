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
	 * @return AbstractNode[]
	 */
	public function simplify(AbstractNode $node) {
		$nodes = $this->recusiveSimplification($node);
		$resultNodes = array();

		foreach($nodes as $node) {
			$resultNodes = array_merge($resultNodes, $this->doSimplification($node));
		}

		return $resultNodes;
	}

	public function doSimplification(AbstractNode $node) {
		foreach($this->simplifiers as $simplifier) {
			if($simplifier->isSimplifierFor($node)) {
				return $simplifier->simplify($node);
			}
		}

		return array($node);
	}

	private function recusiveSimplification(AbstractNode $node) {

		if($node instanceof TripleNode) {
			$nodes = array();
			$subjects = $this->simplify($node->getSubject());
			$objects = $this->simplify($node->getObject());

			foreach($subjects as $subject) {
				foreach($objects as $object) {
					$nodes[] = new TripleNode(
						$subject,
						$node->getPredicate(),
						$object
					);
				}
			}

			return $nodes;
		}

		return array($node);
	}
}
