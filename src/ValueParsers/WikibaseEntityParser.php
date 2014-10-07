<?php

namespace PPP\Wikidata\ValueParsers;

use Mediawiki\Api\MediawikiApi;
use ValueParsers\ParseException;
use ValueParsers\ParserOptions;
use ValueParsers\StringValueParser;
use ValueParsers\ValueParser;
use Wikibase\DataModel\Deserializers\EntityIdDeserializer;

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
	 * @var EntityIdDeserializer
	 */
	private $entityIdDeserializer;

	/**
	 * @param MediaWikiApi $api
	 * @param EntityIdDeserializer $entityIdDeserializer
	 * @param ParserOptions|null $options
	 */
	public function __construct(MediaWikiApi $api, EntityIdDeserializer $entityIdDeserializer, ParserOptions $options = null) {
		$options->requireOption(self::OPT_ENTITY_TYPE);

		$this->api = $api;
		$this->entityIdDeserializer = $entityIdDeserializer;
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
			return $this->entityIdDeserializer->deserialize($entry['id']);
		}

		throw new ParseException('No entity returned.', $value, self::FORMAT_NAME);
	}
}
