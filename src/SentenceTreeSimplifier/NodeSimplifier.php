<?php

namespace PPP\Wikidata\SentenceTreeSimplifier;

use PPP\DataModel\AbstractNode;

/**
 * Interface for simplifiers
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
interface NodeSimplifier {

	/**
	 * @param AbstractNode $node
	 * @return bool
	 */
	public function isSimplifierFor(AbstractNode $node);

	/**
	 * @param AbstractNode $node
	 * @return AbstractNode
	 */
	public function simplify(AbstractNode $node);
}
