<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\Geo\Formatters\GlobeCoordinateFormatter;
use Mediawiki\Api\MediawikiApi;
use PPP\Wikidata\WikibaseEntityProvider;
use ValueFormatters\DecimalFormatter;
use ValueFormatters\FormatterOptions;
use ValueFormatters\QuantityFormatter;
use ValueFormatters\StringFormatter;
use ValueFormatters\TimeFormatter;
use ValueFormatters\ValueFormatter;
use Wikibase\Api\WikibaseFactory;

/**
 * Build a parser for Wikibase value
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseValueFormatterFactory {

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
	 * @return WikibaseValueFormatter
	 */
	public function newWikibaseValueFormatter() {
		$options = $this->newFormatterOptions();

		return new WikibaseValueFormatter(array(
			'globecoordinate' => new GlobeCoordinateFormatter($options),
			'quantity' => new QuantityFormatter(new DecimalFormatter($options), $options),
			'string' => new StringFormatter($options),
			'time' => new TimeFormatter($options),
			'wikibase-entityid' => $this->newWikibaseEntityFormatter($options)
		));
	}

	private function newFormatterOptions() {
		return new FormatterOptions(array(
			ValueFormatter::OPT_LANG => $this->languageCode
		));
	}

	private function newWikibaseEntityFormatter(FormatterOptions $options) {
		$wikibaseFactory = new WikibaseFactory($this->api);
		return new WikibaseEntityFormatter(new WikibaseEntityProvider($wikibaseFactory->newRevisionGetter()), $options);
	}
}
