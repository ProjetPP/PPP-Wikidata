<?php

namespace PPP\Wikidata\TreeSimplifier;

use InvalidArgumentException;
use Mediawiki\Api\MediawikiApi;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\ResourceNode;
use PPP\DataModel\SentenceNode;
use PPP\DataModel\StringResourceNode;
use PPP\DataModel\TripleNode;
use PPP\Module\TreeSimplifier\NodeSimplifier;

/**
 * Simplifies triples with identity predicate or sentence nodes
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class IdentityTripleNodeSimplifier implements NodeSimplifier {

	/**
	 * @var MediawikiApi
	 */
	private $mediawikiApi;

	/**
	 * @param string $languageCode
	 */
	public function __construct($languageCode) {
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
			return $this->doSimplificationForSentence($node);
		} else if($node instanceof TripleNode) {
			return $this->doSimplificationForTriple($node);
		} else {
			return $node;
		}
	}

	private function doSimplificationForSentence(SentenceNode $node) {
		return $this->getDescriptionsForSubjects($this->filterDisambiguation(array($node->getValue())));
	}

	private function doSimplificationForTriple(TripleNode $node) {
		if(!$this->isPredicateIdentity($node->getPredicate())) {
			return $node;
		}

		return $this->getDescriptionsForSubjects(
			$this->filterDisambiguation(
				$this->resourceListToStringArray($node->getSubject())
			)
		);
	}

	private function isPredicateIdentity(ResourceListNode $predicates) {
		if($predicates->count() !== 1) {
			return false;
		}

		/** @var ResourceNode $resource */
		return strtolower($predicates->getIterator()->current()->getValue()) === 'identity';
	}

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
			$descriptions[] = new StringResourceNode($pageResult['extract']);
		}

		return new ResourceListNode($descriptions);
	}

	private function resourceListToStringArray(ResourceListNode $subjects) {
		$titles = array();

		/** @var ResourceNode $subject */
		foreach($subjects as $subject) {
			$titles[] = $subject->getValue();
		}

		return $titles;
	}

	private function getWikipediaApiForLanguage($languageCode) {
		return new MediawikiApi('https://' . $languageCode . '.wikipedia.org/w/api.php');
	}
}
