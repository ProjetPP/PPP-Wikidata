<?php

namespace PPP\Wikidata\TreeSimplifier;

use InvalidArgumentException;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\TripleNode;
use PPP\Module\TreeSimplifier\NodeSimplifier;
use PPP\Wikidata\ValueParsers\ResourceListNodeParser;
use PPP\Wikidata\WikibaseResourceNode;

/**
 * Simplifies a triple node when the object is missing.
 *
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class MissingObjectTripleNodeSimplifier implements NodeSimplifier {

	/**
	 * @var ResourceListNodeParser
	 */
	private $resourceListNodeParser;

	/**
	 * @var ResourceListForEntityProperty
	 */
	private $resourceListForEntityProperty;

	/**
	 * @param ResourceListNodeParser $resourceListNodeParser
	 * @param ResourceListForEntityProperty $resourceListForEntityProperty
	 */
	public function __construct(ResourceListNodeParser $resourceListNodeParser, ResourceListForEntityProperty $resourceListForEntityProperty) {
		$this->resourceListNodeParser = $resourceListNodeParser;
		$this->resourceListForEntityProperty = $resourceListForEntityProperty;
	}

	/**
	 * @see NodeSimplifier::isSimplifierFor
	 */
	public function isSimplifierFor(AbstractNode $node) {
		return $node instanceof TripleNode &&
			$node->getSubject() instanceof ResourceListNode &&
			$node->getPredicate() instanceof ResourceListNode &&
			$node->getObject() instanceof MissingNode;
	}

	/**
	 * @see NodeSimplifier::doSimplification
	 */
	public function simplify(AbstractNode $node) {
		if(!$this->isSimplifierFor($node)) {
			throw new InvalidArgumentException('MissingObjectTripleNodeSimplifier can only simplify TripleNode with a missing object');
		}

		return $this->doSimplification($node);
	}

	private function doSimplification(TripleNode $node) {
		$subjectNodes = $this->resourceListNodeParser->parse($node->getSubject(), 'wikibase-item');
		$propertyNodes = $this->resourceListNodeParser->parse($node->getPredicate(), 'wikibase-property');
		$results = array();

		foreach($subjectNodes as $subject) {
			foreach($propertyNodes as $predicate) {
				$results[] = $this->getNodesForObject($subject, $predicate);
			}
		}

		$node = new ResourceListNode($results);
		return $node;
	}

	protected function getNodesForObject(WikibaseResourceNode $subject, WikibaseResourceNode $predicate) {
		return $this->resourceListForEntityProperty->getForEntityProperty(
			$subject->getDataValue()->getEntityId(),
			$predicate->getDataValue()->getEntityId()
		);
	}
}
