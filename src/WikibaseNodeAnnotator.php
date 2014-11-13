<?php

namespace PPP\Wikidata;

use DataValues\UnknownValue;
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
	 * @return AbstractNode[]
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
				return array($node);
		}
	}

	private function annotateResourceNode(ResourceNode $node, $type) {
		if($type === null) {
			return array(new WikibaseResourceNode(
				$node->getValue(),
				new UnknownValue($node)
			));
		}

		$result = array();
		foreach($this->valueParser->parse($node->getValue(), $type) as $annotation) {
			$result[] = new WikibaseResourceNode(
				$node->getValue(),
				$annotation
			);
		}
		return $result;
	}

	private function annotateTripleNode(TripleNode $node) {
		$subjectNodes = $this->annotateNodeWithType($node->getSubject(), 'wikibase-item');
		$propertyNodes = $this->annotateNodeWithType($node->getPredicate(), 'wikibase-property');
		$objectNodesPerType = array();

		$result = array();
		foreach($subjectNodes as $subjectNode) {
			foreach($propertyNodes as $propertyNode) {
				$objectType = $this->propertyTypeProvider->getTypeForProperty($propertyNode->getDataValue()->getEntityId());
				if(!array_key_exists($objectType, $objectNodesPerType)) {
					$objectNodesPerType[$objectType] = $this->annotateNodeWithType($node->getObject(), $objectType);
				}
				foreach($objectNodesPerType[$objectType] as $objectNode) {
					$result[] = new TripleNode($subjectNode, $propertyNode, $objectNode);
				}
			}
		}
		return $result;
	}
}
