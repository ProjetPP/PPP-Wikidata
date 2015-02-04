<?php

namespace PPP\Wikidata\ValueFormatters;

use Doctrine\Common\Cache\Cache;
use Mediawiki\Api\MediawikiApi;
use PPP\Wikidata\Cache\PerSiteLinkCache;
use PPP\Wikidata\Wikipedia\MediawikiArticleHeaderProvider;
use PPP\Wikidata\Wikipedia\MediawikiArticleImageProvider;
use ValueFormatters\DecimalFormatter;
use ValueFormatters\FormatterOptions;
use ValueFormatters\QuantityFormatter;
use ValueFormatters\ValueFormatter;
use Wikibase\EntityStore\Api\ApiEntityStore;
use Wikibase\EntityStore\Cache\CachedEntityStore;

/**
 * Build a parser for Wikibase value
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseResourceNodeFormatterFactory {

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
	 * @return WikibaseResourceNodeFormatter
	 */
	public function newWikibaseResourceNodeFormatter() {
		$options = $this->newFormatterOptions();

		return new DispatchingWikibaseResourceNodeFormatter(array(
			'globecoordinate' => new GlobeCoordinateFormatter($this->newWikibaseEntityIdJsonLdFormatter($options), $options),
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
			$this->newEntityStore(),
			$this->newWikibaseEntityIdJsonLdFormatter($options),
			$options
		);
	}

	private function newWikibaseEntityIdJsonLdFormatter(FormatterOptions $options) {
		return new WikibaseEntityIdJsonLdFormatter(
			$this->newEntityStore(),
			$this->newMediawikiArticleHeaderProvider(),
			$this->newMediawikiArticleImageProvider(),
			$options
		);
	}

	public function newWikibaseEntityIdFormatterPreloader() {
		return new WikibaseEntityIdFormatterPreloader(
			$this->newEntityStore(),
			array(
				$this->newMediawikiArticleHeaderProvider(),
				$this->newMediawikiArticleImageProvider()
			),
			$this->languageCode
		);
	}

	private function newEntityStore() {
		return new CachedEntityStore(new ApiEntityStore($this->api), $this->cache);

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
