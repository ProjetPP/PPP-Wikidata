<?php

namespace PPP\Wikidata;

use PPP\DataModel\AbstractNode;
use PPP\DataModel\ResourceNode;
use PPP\DataModel\TripleNode;
use PPP\Wikidata\ValueParsers\WikibaseValueParser;

/**
 * Annotates intermediate representation with Wikibase.
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 *
 * @todo Check if nodes with missing element returns the right type
 */
class WikibaseNodeAnnotator {

	/**
	 * @var WikibaseValueParser
	 */
	private $valueParser;

	/**
	 * @var WikibasePropertyTypeProvider
	 */
	private $propertyTypeProvider;

	/**
	 * @param WikibaseValueParser $valueParser
	 * @param WikibasePropertyTypeProvider $propertyTypeProvider
	 */
	public function __construct(WikibaseValueParser $valueParser, WikibasePropertyTypeProvider $propertyTypeProvider) {
		$this->valueParser = $valueParser;
		$this->propertyTypeProvider = $propertyTypeProvider;
	}

	/**
	 * @param AbstractNode $node
	 * @return AbstractNode
	 */
	public function annotateNode(AbstractNode $node) {
		return $this->annotateNodeWithType($node, null);
	}

	private function annotateNodeWithType(AbstractNode $node, $type) {
		switch($node->getType()) {
			case 'resource':
				return $this->annotateResourceNode($node, $type);
			case 'triple':
				return $this->annotateTripleNode($node);
			default:
				return $node;
		}
	}

	private function annotateResourceNode(ResourceNode $node, $type) {
		if($type === null) {
			return $node;
		}

		return new WikibaseResourceNode(
			$node->getValue(),
			$this->valueParser->parse($node->getValue(), $type)
		);
	}

	private function annotateTripleNode(TripleNode $node) {
		$propertyNode = $this->annotateNodeWithType($node->getPredicate(), 'wikibase-property');

		return new TripleNode(
			$this->annotateNodeWithType(
				$node->getSubject(),
				'wikibase-item'
			),
			$propertyNode,
			$this->annotateNodeWithType(
				$node->getObject(),
				$this->propertyTypeProvider->getTypeForProperty($propertyNode->getDataValue()->getEntityId())
			)
		);
	}
}
