<?php

namespace PPP\Wikidata\ValueParsers;

use Mediawiki\Api\MediawikiApi;
use PPP\Wikidata\Cache\WikibaseEntityIdParserCache;
use ValueParsers\ParseException;
use ValueParsers\ParserOptions;
use ValueParsers\StringValueParser;
use ValueParsers\ValueParser;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\DataModel\Entity\EntityIdValue;

/**
 * Try to find a Wikibase entity id from a given string. Only returns the first id found.
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseEntityIdParser extends StringValueParser {

	const FORMAT_NAME = 'wikibase-entity';

	/**
	 * Identifier for the option that holds the type of entity the parser should looks for.
	 */
	const OPT_ENTITY_TYPE = 'type';

	/**
	 * @var MediawikiApi
	 */
	private $api;

	/**
	 * @var EntityIdParser
	 */
	private $entityIdParser;

	/**
	 * @var WikibaseEntityIdParserCache
	 */
	private $entityIdParserCache;

	/**
	 * @param MediaWikiApi $api
	 * @param EntityIdParser $entityIdParser
	 * @param ParserOptions|null $options
	 */
	public function __construct(MediaWikiApi $api, EntityIdParser $entityIdParser, WikibaseEntityIdParserCache $entityIdParserCache, ParserOptions $options = null) {
		$options->requireOption(self::OPT_ENTITY_TYPE);

		$this->api = $api;
		$this->entityIdParser = $entityIdParser;
		$this->entityIdParserCache = $entityIdParserCache;

		parent::__construct($options);
	}

	protected function stringParse($value) {
		$languageCode = $this->getOption(ValueParser::OPT_LANG);
		$entityType = $this->getOption(self::OPT_ENTITY_TYPE);

		if($this->entityIdParserCache->contains($value, $entityType, $languageCode)) {
			$result = $this->entityIdParserCache->fetch($value, $entityType, $languageCode);
		} else {
			$result = $this->parseResult($this->doQuery($value, $entityType, $languageCode), $value);
			$this->entityIdParserCache->save($value, $entityType, $languageCode, $result);
		}

		if(empty($result)) {
			throw new ParseException('No entity returned.', $value, self::FORMAT_NAME);
		}

		return $result;
	}

	protected function doQuery($search, $entityType, $languageCode) {
		$params = array(
			'search' => $search,
			'language' => $languageCode,
			'type' => $entityType,
			'limit' => 50
		);
		return $this->api->getAction('wbsearchentities', $params);
	}

	private function parseResult(array $result, $search) {
		$search = $this->cleanLabel($search);

		$results = $this->filterResults($result['search'], $search, true);
		if(empty($results)) {
			$results = $this->filterResults($result['search'], $search, false);
		}

		$entityIds = array();
		foreach($results as $entry) {
			$entityIds[] = new EntityIdValue($this->entityIdParser->parse($entry['id']));
		}

		return $entityIds;
	}

	private function filterResults(array $results, $search, $isStrict) {
		$filtered = array();
		foreach($results as $entry) {
			if($this->doResultsMatch($entry, $search, $isStrict)) {
				$filtered[] = $entry;
			}
		}

		return $filtered;
	}

	private function doResultsMatch(array $entry, $search, $isStrict) {
		if(array_key_exists('aliases', $entry)) {
			foreach($entry['aliases'] as $alias) {
				if($this->areSimilar($this->cleanLabel($alias), $search, $isStrict)) {
					return true;
				}
			}
		}

		return array_key_exists('label', $entry) &&
			$this->areSimilar($this->cleanLabel($entry['label']), $search, $isStrict);
	}

	private function areSimilar($a, $b, $isStrict) {
		if($isStrict) {
			return $a === $b;
		} else {
			//checks if the strings have less than 3 character different and more than 80% percent of characters similar
			return similar_text($a, $b, $percentage) - strlen($a) < 3 &&
				$percentage > 80;
		}
	}

	private function cleanLabel($label) {
		$label = mb_strtolower($label, 'UTF-8');
		$label = preg_replace('/(\(.*\))/', '', $label); //Removes comments
		$label = str_replace(
			array('\'', '-'),
			array(' ', ' '),
			$label
		);
		return trim($label);
	}
}
