<?php

namespace PPP\Wikidata\TreeSimplifier;

use InvalidArgumentException;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\SentenceNode;
use PPP\DataModel\StringResourceNode;
use PPP\Module\TreeSimplifier\NodeSimplifier;
use PPP\Wikidata\ValueParsers\ResourceListNodeParser;

/**
 * Tries to cast the input sentence to a Wikibase item
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class SentenceNodeSimplifier implements NodeSimplifier {

	/**
	 * @var ResourceListNodeParser
	 */
	private $resourceListNodeParser;

	/**
	 * @param ResourceListNodeParser $resourceListNodeParser
	 */
	public function __construct(ResourceListNodeParser $resourceListNodeParser) {
		$this->resourceListNodeParser = $resourceListNodeParser;
	}

	/**
	 * @see NodeSimplifier::isSimplifierFor
	 */
	public function isSimplifierFor(AbstractNode $node) {
		return $node instanceof SentenceNode;
	}

	/**
	 * @see NodeSimplifier::doSimplification
	 */
	public function simplify(AbstractNode $node) {
		if(!$this->isSimplifierFor($node)) {
			throw new InvalidArgumentException('SentenceNodeSimplifier can only simplify SentenceNode objects');
		}

		return $this->doSimplification($node);
	}

	public function doSimplification(SentenceNode $node) {
		return $this->resourceListNodeParser->parse(
			new ResourceListNode(array(new StringResourceNode($node->getValue()))),
			'wikibase-item'
		);
	}
}
