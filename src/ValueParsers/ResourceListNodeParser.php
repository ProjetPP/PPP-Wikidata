<?php

namespace PPP\Wikidata\ValueParsers;

use PPP\DataModel\ResourceListNode;
use PPP\DataModel\ResourceNode;
use PPP\Wikidata\WikibaseResourceNode;
use ValueParsers\ParseException;

/**
 * Parse ResourceListNode to map it to Wikibase DataValues
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class ResourceListNodeParser {

	/**
	 * @var WikibaseValueParser
	 */
	private $valueParser;

	/**
	 * @param WikibaseValueParser $valueParser
	 */
	public function __construct(WikibaseValueParser $valueParser) {
		$this->valueParser = $valueParser;
	}

	public function parse(ResourceListNode $node, $type) {
		$annotated = array();

		foreach($node as $resource) {
			if($resource instanceof WikibaseResourceNode) {
				$annotated[] = $resource; //TODO: check if it has the same type
			} else {
				try {
					$annotated[] = $this->annotateResourceNode($resource, $type);
				} catch (ParseException $e) {
					//We ignore this value
				}
			}
		}

		return new ResourceListNode($annotated);
	}

	private function annotateResourceNode(ResourceNode $node, $type) {
		$result = array();

		foreach($this->valueParser->parse($node->getValue(), $type) as $annotation) {
			$result[] = new WikibaseResourceNode(
				$node->getValue(),
				$annotation
			);
		}

		return new ResourceListNode($result);
	}
}
