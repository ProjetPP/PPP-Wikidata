<?php

namespace PPP\Wikidata;

use InvalidArgumentException;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\ResourceListNode;
use PPP\Module\TreeSimplifier\NodeSimplifier;
use PPP\Wikidata\ValueFormatters\WikibaseEntityIdFormatterPreloader;
use PPP\Wikidata\ValueFormatters\WikibaseResourceNodeFormatter;

/**
 * Formats ResourceListNodes
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class ResourceListNodeFormatter implements NodeSimplifier {

	/**
	 * @var WikibaseResourceNodeFormatter
	 */
	private $valueFormatter;

	/**
	 * @var WikibaseEntityIdFormatterPreloader
	 */
	private $entityIdFormatterPreloader;

	/**
	 * @param WikibaseResourceNodeFormatter $valueFormatter
	 * @param WikibaseEntityIdFormatterPreloader $entityIdFormatterPreloader
	 */
	public function __construct(WikibaseResourceNodeFormatter $valueFormatter, WikibaseEntityIdFormatterPreloader $entityIdFormatterPreloader) {
		$this->valueFormatter = $valueFormatter;
		$this->entityIdFormatterPreloader = $entityIdFormatterPreloader;
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

		$this->entityIdFormatterPreloader->preload($node);

		return $this->doSimplification($node);
	}

	private function doSimplification(ResourceListNode $node) {
		$resources = array();

		/** @var WikibaseResourceNode $resource */
		foreach($node as $resource) {
			if($resource instanceof WikibaseResourceNode) {
				$resources[] = $this->valueFormatter->format($resource);
			} else {
				$resources[] = $resource;
			}
		}

		return new ResourceListNode($resources);
	}
}
