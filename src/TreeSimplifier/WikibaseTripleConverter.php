<?php

namespace PPP\Wikidata\TreeSimplifier;

use InvalidArgumentException;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\TripleNode;
use PPP\DataModel\UnionNode;
use PPP\Module\TreeSimplifier\NodeSimplifier;
use PPP\Wikidata\ValueParsers\ResourceListNodeParser;
use PPP\Wikidata\WikibasePropertyTypeProvider;
use PPP\Wikidata\WikibaseResourceNode;

/**
 * Annotates triples with Wikibase DataValues
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseTripleConverter implements NodeSimplifier {

	/**
	 * @var ResourceListNodeParser
	 */
	private $resourceListNodeParser;

	/**
	 * @var WikibasePropertyTypeProvider
	 */
	private $propertyTypeProvider;

	/**
	 * @param ResourceListNodeParser $resourceListNodeParser
	 * @param WikibasePropertyTypeProvider $propertyTypeProvider
	 */
	public function __construct(ResourceListNodeParser $resourceListNodeParser, WikibasePropertyTypeProvider $propertyTypeProvider) {
		$this->resourceListNodeParser = $resourceListNodeParser;
		$this->propertyTypeProvider = $propertyTypeProvider;
	}

	/**
	 * @see NodeSimplifier::isSimplifierFor
	 */
	public function isSimplifierFor(AbstractNode $node) {
		return $node instanceof TripleNode &&
		($node->getSubject() instanceof ResourceListNode || $node->getSubject() instanceof MissingNode) &&
		($node->getPredicate() instanceof ResourceListNode || $node->getPredicate() instanceof MissingNode) &&
		($node->getObject() instanceof ResourceListNode || $node->getObject() instanceof MissingNode);
	}

	/**
	 * @see NodeSimplifier::doSimplification
	 */
	public function simplify(AbstractNode $node) {
		if(!$this->isSimplifierFor($node)) {
			throw new InvalidArgumentException('MissingObjectTripleNodeSimplifier can only simplify TripleNode with a missing object');
		}

		return $this->doConversion($node);
	}

	private function doConversion(TripleNode $node) {
		$subjectNodes = $this->annotateNodeWithType($node->getSubject(), 'wikibase-item');
		$propertyNodes = $this->annotateNodeWithType($node->getPredicate(), 'wikibase-property');

		$result = array();
		foreach($this->bagsPropertiesPerType($propertyNodes) as $objectType => $propertyNodes) {
			$objectNodes = $this->annotateNodeWithType($node->getObject(), $objectType);
			$result[] = new TripleNode($subjectNodes, new ResourceListNode($propertyNodes), $objectNodes);
		}

		if(count($result) === 1) {
			return $result[0];
		} else {
			return new UnionNode($result);
		}
	}

	private function bagsPropertiesPerType($propertyNodes) {
		$propertyNodesPerType = array();

		/** @var WikibaseResourceNode $propertyNode */
		foreach($propertyNodes as $propertyNode) {
			$objectType = $this->propertyTypeProvider->getTypeForProperty($propertyNode->getDataValue()->getEntityId());
			$propertyNodesPerType[$objectType][] = $propertyNode;
		}

		return $propertyNodesPerType;
	}

	/**
	 * @return AbstractNode
	 */
	private function annotateNodeWithType(AbstractNode $node, $type) {
		if($node instanceof ResourceListNode) {
			return $this->resourceListNodeParser->parse($node, $type);
		} else {
			return $node;
		}
	}
}
