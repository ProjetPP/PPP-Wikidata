<?php

namespace PPP\Wikidata;

use DataValues\DataValue;
use DataValues\StringValue;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\ResourceNode;
use PPP\DataModel\TripleNode;
use RuntimeException;
use ValueParsers\ValueParser;
use Wikibase\DataModel\Entity\EntityIdValue;

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
	 * @var ValueParser
	 */
	private $itemParser;

	/**
	 * @var ValueParser
	 */
	private $propertyParser;

	/**
	 * @var WikibasePropertyTypeProvider
	 */
	private $propertyTypeProvider;

	/**
	 * @param ValueParser $itemParser
	 * @param ValueParser $propertyParser
	 * @param WikibasePropertyTypeProvider $propertyTypeProvider
	 */
	public function __construct(ValueParser $itemParser, ValueParser $propertyParser, WikibasePropertyTypeProvider $propertyTypeProvider) {
		$this->itemParser = $itemParser;
		$this->propertyParser = $propertyParser;
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
			$this->parseStringAsDataValue($node->getValue(), $type)
		);
	}

	/**
	 * @return DataValue
	 */
	private function parseStringAsDataValue($string, $type) {
		switch($type) {
			case 'wikibase-item':
				return new EntityIdValue($this->itemParser->parse($string));
			case 'wikibase-property':
				return new EntityIdValue($this->propertyParser->parse($string));
			case 'string':
				return new StringValue($string);
			default:
				throw new RuntimeException('Unknown value type: ' . $type);
		}
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
