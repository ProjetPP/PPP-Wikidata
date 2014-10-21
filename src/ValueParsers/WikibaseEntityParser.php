<?php

namespace PPP\Wikidata\ValueParsers;

use Mediawiki\Api\MediawikiApi;
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
class WikibaseEntityParser extends StringValueParser {

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
	 * @param MediaWikiApi $api
	 * @param EntityIdParser $entityIdParser
	 * @param ParserOptions|null $options
	 */
	public function __construct(MediaWikiApi $api, EntityIdParser $entityIdParser, ParserOptions $options = null) {
		$options->requireOption(self::OPT_ENTITY_TYPE);

		$this->api = $api;
		$this->entityIdParser = $entityIdParser;
		parent::__construct($options);
	}

	protected function stringParse($value) {
		$params = array(
			'search' => $value,
			'language' => $this->getOption(ValueParser::OPT_LANG),
			'type' => $this->getOption(self::OPT_ENTITY_TYPE)
		);
		$result = $this->api->getAction('wbsearchentities', $params);

		foreach($result['search'] as $entry) {
			return new EntityIdValue($this->entityIdParser->parse($entry['id']));
		}

		throw new ParseException('No entity returned.', $value, self::FORMAT_NAME);
	}
}
