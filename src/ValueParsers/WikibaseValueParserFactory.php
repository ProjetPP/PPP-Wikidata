<?php

namespace PPP\Wikidata\ValueParsers;

use DataValues\Geo\Parsers\GlobeCoordinateParser;
use Mediawiki\Api\MediawikiApi;
use ValueParsers\ParserOptions;
use ValueParsers\ValueParser;
use Wikibase\DataModel\Entity\BasicEntityIdParser;

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
	 * @var MediawikiApi
	 */
	private $api;

	/**
	 * @param $languageCode
	 * @param MediawikiApi $api
	 */
	public function __construct($languageCode, MediawikiApi $api) {
		$this->languageCode = $languageCode;
		$this->api = $api;
	}

	/**
	 * @return WikibaseValueParser
	 */
	public function newWikibaseValueParser() {
		return new WikibaseValueParser(array(
			'string' => new StringParser(),
			'wikibase-item' => $this->newWikibaseEntityParser('item'),
			'wikibase-property' => $this->newWikibaseEntityParser('property'),
			'globecoordinate' => new GlobeCoordinateParser()
		));
	}

	private function newWikibaseEntityParser($type) {
		$parserOptions = new ParserOptions(array(
			ValueParser::OPT_LANG => $this->languageCode,
			WikibaseEntityIdParser::OPT_ENTITY_TYPE => $type
		));
		return new WikibaseEntityIdParser($this->api, new BasicEntityIdParser(), $parserOptions);
	}
}
