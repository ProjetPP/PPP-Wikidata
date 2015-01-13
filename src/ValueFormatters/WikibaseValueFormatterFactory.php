<?php

namespace PPP\Wikidata\ValueFormatters;

use Doctrine\Common\Cache\Cache;
use Mediawiki\Api\MediawikiApi;
use PPP\Wikidata\Cache\PerSiteLinkCache;
use PPP\Wikidata\Cache\WikibaseEntityCache;
use PPP\Wikidata\WikibaseEntityProvider;
use PPP\Wikidata\Wikipedia\MediawikiArticleHeaderProvider;
use PPP\Wikidata\Wikipedia\MediawikiArticleImageProvider;
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
	 * @var MediawikiApi[]
	 */
	private $sitesApi;

	/**
	 * @var Cache
	 */
	private $cache;

	/**
	 * @param $languageCode
	 * @param MediawikiApi $api
	 * @param MediawikiApi[] $sitesApi
	 * @param Cache $cache
	 */
	public function __construct($languageCode, MediawikiApi $api, array $sitesApi, Cache $cache) {
		$this->languageCode = $languageCode;
		$this->api = $api;
		$this->sitesApi = $sitesApi;
		$this->cache = $cache;
	}

	/**
	 * @return WikibaseValueFormatter
	 */
	public function newWikibaseValueFormatter() {
		$options = $this->newFormatterOptions();

		return new WikibaseValueFormatter(array(
			'globecoordinate' => new GlobeCoordinateFormatter($options),
			'monolingualtext' => new MonolingualTextFormatter($options),
			'quantity' => new ToStringFormatter(new QuantityFormatter(new DecimalFormatter($options), $options)),
			'string' => new StringFormatter($options),
			'time' => new TimeFormatter($options),
			'unknown' => new UnknownFormatter($options),
			'wikibase-entityid' => $this->newWikibaseEntityFormatter($options)
		));
	}

	private function newFormatterOptions() {
		return new FormatterOptions(array(
			ValueFormatter::OPT_LANG => $this->languageCode
		));
	}

	private function newWikibaseEntityFormatter(FormatterOptions $options) {
		return new WikibaseEntityIdFormatter(
			$this->newWikibaseEntityProvider(),
			$this->newMediawikiArticleHeaderProvider(),
			$this->newMediawikiArticleImageProvider(),
			$options
		);
	}

	public function newWikibaseEntityIdFormatterPreloader() {
		return new WikibaseEntityIdFormatterPreloader(
			$this->newWikibaseEntityProvider(),
			array(
				$this->newMediawikiArticleHeaderProvider(),
				$this->newMediawikiArticleImageProvider()
			),
			$this->languageCode
		);
	}

	private function newWikibaseEntityProvider() {
		$wikibaseFactory = new WikibaseFactory($this->api);

		return new WikibaseEntityProvider(
			$wikibaseFactory->newRevisionsGetter(),
			new WikibaseEntityCache($this->cache)
		);
	}

	private function newMediawikiArticleHeaderProvider() {
		return new MediawikiArticleHeaderProvider(
			$this->sitesApi,
			new PerSiteLinkCache($this->cache, 'wparticlehead')
		);
	}

	private function newMediawikiArticleImageProvider() {
		return new MediawikiArticleImageProvider(
			$this->sitesApi,
			new PerSiteLinkCache($this->cache, 'wpimg')
		);
	}
}
