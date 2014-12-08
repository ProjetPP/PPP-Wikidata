<?php

namespace PPP\Wikidata\TreeSimplifier;

use InvalidArgumentException;
use Mediawiki\Api\MediawikiApi;
use OutOfBoundsException;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\ResourceNode;
use PPP\DataModel\SentenceNode;
use PPP\DataModel\StringResourceNode;
use PPP\DataModel\TripleNode;
use PPP\Module\TreeSimplifier\NodeSimplifier;
use PPP\Wikidata\ValueParsers\ResourceListNodeParser;
use PPP\Wikidata\WikibaseEntityProvider;
use PPP\Wikidata\WikibaseResourceNode;
use ValueParsers\ParseException;
use Wikibase\DataModel\Entity\ItemId;

/**
 * Simplifies triples with identity predicate or sentence nodes
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class IdentityTripleNodeSimplifier implements NodeSimplifier {

	/**
	 * @var ResourceListNodeParser
	 */
	private $resourceListNodeParser;

	/**
	 * @var WikibaseEntityProvider
	 */
	private $entityProvider;

	/**
	 * @var string
	 */
	private $languageCode;

	/**
	 * @var MediawikiApi
	 */
	private $mediawikiApi;

	/**
	 * @param ResourceListNodeParser $resourceListNodeParser
	 * @param WikibaseEntityProvider $entityProvider
	 * @param string $languageCode
	 */
	public function __construct(ResourceListNodeParser $resourceListNodeParser, WikibaseEntityProvider $entityProvider, $languageCode) {
		$this->resourceListNodeParser = $resourceListNodeParser;
		$this->entityProvider = $entityProvider;
		$this->languageCode = $languageCode;
		$this->mediawikiApi = $this->getWikipediaApiForLanguage($languageCode);
	}

	/**
	 * @see NodeSimplifier::isSimplifierFor
	 */
	public function isSimplifierFor(AbstractNode $node) {
		return $node instanceof SentenceNode || (
			$node instanceof TripleNode &&
			$node->getSubject() instanceof ResourceListNode &&
			$node->getPredicate() instanceof ResourceListNode &&
			$node->getObject() instanceof MissingNode
		);
	}

	/**
	 * @see NodeSimplifier::doSimplification
	 */
	public function simplify(AbstractNode $node) {
		if(!$this->isSimplifierFor($node)) {
			throw new InvalidArgumentException('IdentityTripleNodeSimplifier can only simplify TripleNode with a missing object');
		}

		if($node instanceof SentenceNode) {
			return $this->doSimplificationForResourceList(new ResourceListNode(array(new StringResourceNode($node->getValue()))));
		} else if($node instanceof TripleNode) {
			return $this->doSimplificationForTriple($node);
		} else {
			return $node;
		}
	}

	private function doSimplificationForTriple(TripleNode $node) {
		if(!$this->isPredicateIdentity($node->getPredicate())) {
			return $node;
		}

		return $this->doSimplificationForResourceList($node->getSubject());
	}

	private function doSimplificationForResourceList(ResourceListNode $resourceList) {
		try {
			$wikibaseResources = $this->resourceListNodeParser->parse($resourceList, 'wikibase-item');
		} catch(ParseException $e) {
			return new ResourceListNode();
		}

		$titles = $this->filterDisambiguation($this->getTitlesForWikibaseResources($wikibaseResources));

		return $this->getDescriptionsForSubjects($titles);
	}

	private function isPredicateIdentity(ResourceListNode $predicates) {
		if($predicates->count() !== 1) {
			return false;
		}

		/** @var ResourceNode $resource */
		return strtolower($predicates->getIterator()->current()->getValue()) === 'identity';
	}

	private function getTitlesForWikibaseResources(ResourceListNode $resourceList) {
		$titles = array();

		/** @var WikibaseResourceNode $resource */
		foreach($resourceList as $resource) {
			/** @var ItemId $itemId */
			$itemId = $resource->getDataValue()->getEntityId();
			$item = $this->entityProvider->getItem($itemId);
			try {
				$titles[] = $item->getSiteLinkList()->getBySiteId($this->languageCode . 'wiki')->getPageName();
			} catch(OutOfBoundsException $e) {
			}
		}

		return $titles;
	}

	//TODO: use Wikidata content
	private function filterDisambiguation(array $titles) {
		$result = $this->mediawikiApi->getAction('query', array(
			'titles' => implode('|', $titles),
			'prop' => 'pageprops',
			'redirects' => true,
			'ppprop' => 'disambiguation'
		));

		$filteredTitles = array();
		foreach($result['query']['pages'] as $id => $pageResult) {
			if(
				$id > 0 &&
				(!array_key_exists('pageprops', $pageResult) || !array_key_exists('disambiguation', $pageResult['pageprops']))
			) {
				$filteredTitles[] = $pageResult['title'];
			}
		}

		return $filteredTitles;
	}

	private function getDescriptionsForSubjects(array $titles) {
		if(empty($titles)) {
			return new ResourceListNode();
		}

		$result = $this->mediawikiApi->getAction('query', array(
			'titles' => implode('|', $titles),
			'prop' => 'extracts',
			'redirects' => true,
			'exintro' => true,
			'exsectionformat' => 'plain',
			'explaintext' => true,
			'exsentences' => 3
		));

		$descriptions = array();

		foreach($result['query']['pages'] as $pageResult) {
			if(array_key_exists('extract', $pageResult)) {
				$descriptions[] = new StringResourceNode($pageResult['extract']);
			}
		}

		return new ResourceListNode($descriptions);
	}

	private function getWikipediaApiForLanguage($languageCode) {
		return new MediawikiApi('https://' . $languageCode . '.wikipedia.org/w/api.php');
	}
}
