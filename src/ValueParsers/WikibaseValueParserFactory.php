<?php

namespace PPP\Wikidata\ValueParsers;

use DataValues\Geo\Parsers\GlobeCoordinateParser;
use ValueParsers\ParserOptions;
use ValueParsers\ValueParser;
use Wikibase\EntityStore\EntityStore;

/**
 * Build a parser for Wikibase value
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseValueParserFactory {

	/**
	 * @var string language code
	 */
	private $languageCode;

	/**
	 * @var EntityStore
	 */
	private $entityStore;

	/**
	 * @param $languageCode
	 * @param EntityStore $entityStore
	 */
	public function __construct($languageCode, EntityStore $entityStore) {
		$this->languageCode = $languageCode;
		$this->entityStore = $entityStore;
	}

	/**
	 * @return WikibaseValueParser
	 */
	public function newWikibaseValueParser() {
		return new WikibaseValueParser(array(
			'commonsMedia' => new StringParser(),
			'external-id' => new StringParser(),
			'globe-coordinate' => new GlobeCoordinateParser(),
			//TODO 'quantity' => ,
			'monolingualtext' => new MonolingualTextParser(),
			'string' => new StringParser(),
			//TODO 'time' => ,
			'url' => new StringParser(),
			'wikibase-item' => $this->newWikibaseEntityParser('item'),
			'wikibase-property' => $this->newWikibaseEntityParser('property')
		));
	}

	private function newWikibaseEntityParser($type) {
		$parserOptions = new ParserOptions(array(
			ValueParser::OPT_LANG => $this->languageCode,
			WikibaseEntityIdParser::OPT_ENTITY_TYPE => $type
		));

		return new WikibaseEntityIdParser(
			$this->entityStore,
			$parserOptions
		);
	}
}
