<?php

namespace PPP\Wikidata;

use InvalidArgumentException;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\ResourceListNode;
use PPP\Module\TreeSimplifier\NodeSimplifier;
use PPP\Wikidata\ValueFormatters\WikibaseValueFormatter;

/**
 * Formats ResourceListNodes
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class ResourceListNodeFormatter implements NodeSimplifier {

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
	 * @see NodeSimplifier::isSimplifierFor
	 */
	public function isSimplifierFor(AbstractNode $node) {
		return $node instanceof ResourceListNode;
	}

	/**
	 * @see NodeSimplifier::doSimplification
	 */
	public function simplify(AbstractNode $node) {
		if(!$this->isSimplifierFor($node)) {
			throw new InvalidArgumentException('ResourceListNodeSimplifier can only simplify ResourceListNode');
		}

		return $this->doSimplification($node);
	}

	private function doSimplification(ResourceListNode $node) {
		$resources = array();

		/** @var WikibaseResourceNode $resource */
		foreach($node as $resource) {
			if($resource instanceof WikibaseResourceNode) {
				$resources[] = $this->valueFormatter->format($resource->getDataValue());
			} else {
				$resources[] = $resource;
			}
		}

		return new ResourceListNode($resources);
	}
}
