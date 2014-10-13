<?php

namespace PPP\Wikidata;

use PPP\DataModel\AbstractNode;
use PPP\DataModel\TripleNode;
use PPP\Wikidata\ValueFormatters\WikibaseValueFormatter;

/**
 * Formats WikibaseNode for output
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseNodeFormatter {

	/**
	 * @var WikibaseValueFormatter
	 */
	private $valueFormatter;

	/**
	 * @param WikibaseValueFormatter $valueFormatter
	 */
	public function __construct(WikibaseValueFormatter $valueFormatter) {
		$this->valueFormatter = $valueFormatter;
	}

	/**
	 * @param AbstractNode $node
	 * @return AbstractNode
	 */
	public function formatNode(AbstractNode $node) {
		switch($node->getType()) {
			case 'resource':
				return $this->formatResourceNode($node);
			case 'triple':
				return $this->formatTripleNode($node);
			default:
				return $node;
		}
	}

	private function formatResourceNode(WikibaseResourceNode $node) {
		if($node->getValue() !== '') {
			return $node;
		}

		return new WikibaseResourceNode(
			$this->valueFormatter->format($node->getDataValue()),
			$node->getDataValue()
		);
	}

	private function formatTripleNode(TripleNode $node) {
		return new TripleNode(
			$this->formatNode($node->getSubject()),
			$this->formatNode($node->getPredicate()),
			$this->formatNode($node->getObject())
		);
	}
}
