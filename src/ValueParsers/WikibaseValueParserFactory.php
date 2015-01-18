<?php

namespace PPP\Wikidata\ValueParsers;

use DataValues\Geo\Parsers\GlobeCoordinateParser;
use Doctrine\Common\Cache\Cache;
use Mediawiki\Api\MediawikiApi;
use PPP\Wikidata\Cache\WikibaseEntityCache;
use PPP\Wikidata\Cache\WikibaseEntityIdParserCache;
use PPP\Wikidata\WikibaseEntityProvider;
use ValueParsers\ParserOptions;
use ValueParsers\ValueParser;
use Wikibase\Api\WikibaseFactory;
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
	 * @var Cache
	 */
	private $cache;

	/**
	 * @param $languageCode
	 * @param MediawikiApi $api
	 */
	public function __construct($languageCode, MediawikiApi $api, Cache $cache) {
		$this->languageCode = $languageCode;
		$this->api = $api;
		$this->cache = $cache;
	}

	/**
	 * @return WikibaseValueParser
	 */
	public function newWikibaseValueParser() {
		return new WikibaseValueParser(array(
			'commonsMedia' => new StringParser(),
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
		$wikibaseFactory = new WikibaseFactory($this->api);
		return new WikibaseEntityIdParser(
			$this->api,
			new BasicEntityIdParser(),
			new WikibaseEntityIdParserCache($this->cache),
			new WikibaseEntityProvider($wikibaseFactory->newRevisionsGetter(), new WikibaseEntityCache($this->cache)),
			$parserOptions
		);
	}
}
