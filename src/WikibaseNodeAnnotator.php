<?php

namespace PPP\Wikidata;

use DataValues\UnknownValue;
use InvalidArgumentException;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\ResourceNode;
use PPP\DataModel\TripleNode;
use PPP\DataModel\UnionNode;
use PPP\Wikidata\ValueParsers\WikibaseValueParser;

/**
 * Annotates intermediate representation with Wikibase.
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 *
 * @todo Check if nodes with missing element returns the right type
 * @todo support annotation of union, intersection...
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

	/**
	 * @return AbstractNode
	 */
	private function annotateNodeWithType(AbstractNode $node, $type) {
		switch($node->getType()) {
			case 'list':
				return $this->annotateResourceListNode($node, $type);
			case 'triple':
				return $this->annotateTripleNode($node, $type);
			case 'missing':
				return $node;
			default:
				throw new InvalidArgumentException('Unsupported node type ' . $node->getType());
		}
	}

	private function annotateResourceListNode(ResourceListNode $node, $type) {
		$annotated = array();

		foreach($node as $resource) {
			$annotated[] = $this->annotateResourceNode($resource, $type);
		}

		return new ResourceListNode($annotated);
	}

	private function annotateResourceNode(ResourceNode $node, $type) {
		if($type === null) {
			return new WikibaseResourceNode(
				$node->getValue(),
				new UnknownValue($node)
			);
		}

		$result = array();
		foreach($this->valueParser->parse($node->getValue(), $type) as $annotation) {
			$result[] = new WikibaseResourceNode(
				$node->getValue(),
				$annotation
			);
		}
		return new ResourceListNode($result);
	}

	private function annotateTripleNode(TripleNode $node) {
		$subjectNodes = $this->annotateNodeWithType($node->getSubject(), 'wikibase-item');
		$propertyNodes = $this->annotateNodeWithType($node->getPredicate(), 'wikibase-property');
		$propertyNodesPerType = array();

		foreach($propertyNodes as $propertyNode) {
			$objectType = $this->propertyTypeProvider->getTypeForProperty($propertyNode->getDataValue()->getEntityId());
			$propertyNodesPerType[$objectType][] = $propertyNode;
		}

		$result = array();
		foreach($propertyNodesPerType as $objectType => $propertyNodes) {
			$objectNodes = $this->annotateNodeWithType($node->getObject(), $objectType);
			$result[] = new TripleNode($subjectNodes, new ResourceListNode($propertyNodes), $objectNodes);
		}

		return new UnionNode($result);
	}
}
