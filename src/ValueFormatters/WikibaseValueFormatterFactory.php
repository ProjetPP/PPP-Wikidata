<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\Geo\Formatters\GlobeCoordinateFormatter;
use Doctrine\Common\Cache\Cache;
use Mediawiki\Api\MediawikiApi;
use PPP\Wikidata\Cache\WikibaseEntityCache;
use PPP\Wikidata\WikibaseEntityProvider;
use ValueFormatters\DecimalFormatter;
use ValueFormatters\FormatterOptions;
use ValueFormatters\QuantityFormatter;
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
	 * @var Cache
	 */
	private $cache;

	/**
	 * @param $languageCode
	 * @param MediawikiApi $api
	 * @param Cache $cache
	 */
	public function __construct($languageCode, MediawikiApi $api, Cache $cache) {
		$this->languageCode = $languageCode;
		$this->api = $api;
		$this->cache = $cache;
	}

	/**
	 * @return WikibaseValueFormatter
	 */
	public function newWikibaseValueFormatter() {
		$options = $this->newFormatterOptions();

		return new WikibaseValueFormatter(array(
			'globecoordinate' => new ToStringFormatter(new GlobeCoordinateFormatter($options)),
			'monolingualtext' => new MonolingualTextFormatter($options),
			'quantity' => new ToStringFormatter(new QuantityFormatter(new DecimalFormatter($options), $options)),
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
		$entityProvider = new WikibaseEntityProvider(
			$wikibaseFactory->newRevisionGetter(),
			new WikibaseEntityCache($this->cache)
		);
		return new WikibaseEntityFormatter($entityProvider, $options);
	}
}
