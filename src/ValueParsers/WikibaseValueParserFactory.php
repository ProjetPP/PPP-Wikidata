<?php

namespace PPP\Wikidata\ValueParsers;

use DataValues\Geo\Parsers\GlobeCoordinateParser;
use ValueParsers\DispatchingValueParser;
use ValueParsers\EraParser;
use ValueParsers\IsoTimestampParser;
use ValueParsers\MonolingualMonthNameProvider;
use ValueParsers\MonthNameUnlocalizer;
use ValueParsers\ParserOptions;
use ValueParsers\PhpDateTimeParser;
use ValueParsers\QuantityParser;
use ValueParsers\ValueParser;
use ValueParsers\YearMonthDayTimeParser;
use ValueParsers\YearMonthTimeParser;
use ValueParsers\YearTimeParser;
use Wikibase\EntityStore\EntityStore;

/**
 * Build a parser for Wikibase value
 *
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class WikibaseValueParserFactory {

	private static $MONTH_NAMES = array(
		1 => 'January',
		2 => 'February',
		3 => 'March',
		4 => 'April',
		5 => 'May',
		6 => 'June',
		7 => 'July',
		8 => 'August',
		9 => 'September',
		10 => 'October',
		11 => 'November',
		12 => 'December'
	);

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
			'quantity' => new QuantityParser(),
			'monolingualtext' => new MonolingualTextParser(),
			'string' => new StringParser(),
			'time' => new DispatchingValueParser(array(
				new IsoTimestampParser(),
				new YearMonthDayTimeParser(),
				new YearMonthTimeParser(new MonolingualMonthNameProvider(self::$MONTH_NAMES)), //TODO: localisation
				new YearTimeParser(),
				new PhpDateTimeParser(new MonthNameUnlocalizer(array()), new EraParser(), new IsoTimestampParser())
			), 'time'),
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
