<?php

namespace PPP\Wikidata\ValueFormatters;

use Doctrine\Common\Cache\Cache;
use Mediawiki\Api\MediawikiApi;
use PPP\Wikidata\Cache\PerSiteLinkCache;
use PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdEntityFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdEntityIdFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdItemFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdPropertyFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdDecimalFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdGlobeCoordinateFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdMonolingualTextFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdQuantityFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdStringFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdTimeFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdUnknownFormatter;
use PPP\Wikidata\Wikipedia\MediawikiArticleHeaderProvider;
use PPP\Wikidata\Wikipedia\MediawikiArticleImageProvider;
use ValueFormatters\DecimalFormatter;
use ValueFormatters\FormatterOptions;
use ValueFormatters\QuantityFormatter;
use ValueFormatters\ValueFormatter;
use Wikibase\EntityStore\EntityStore;

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
	 * @var EntityStore
	 */
	private $entityStore;

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
	 * @param EntityStore $entityStore
	 * @param MediawikiApi[] $sitesApi
	 * @param Cache $cache
	 */
	public function __construct($languageCode, EntityStore $entityStore, array $sitesApi, Cache $cache) {
		$this->languageCode = $languageCode;
		$this->entityStore = $entityStore;
		$this->sitesApi = $sitesApi;
		$this->cache = $cache;
	}

	/**
	 * @return ValueFormatter
	 */
	public function newWikibaseResourceNodeFormatter() {
		$options = $this->newFormatterOptions();

		return new DispatchingWikibaseResourceNodeFormatter(array(
			'globecoordinate' => $this->newGlobeCoordinateFormatter($options),
			'monolingualtext' => new JsonLdLiteralFormatter(new JsonLdMonolingualTextFormatter($options), $options),
			'quantity' => $this->newQuantityFormatter($options),
			'string' => new JsonLdLiteralFormatter(new JsonLdStringFormatter($options), $options),
			'time' => new JsonLdLiteralFormatter(new JsonLdTimeFormatter(new IsoTimeFormatter($options), $options), $options),
			'unknown' => new JsonLdLiteralFormatter(new JsonLdUnknownFormatter($options), $options),
			'wikibase-entityid' => $this->newEntityIdValueFormatter($options)
		));
	}

	private function newFormatterOptions() {
		return new FormatterOptions(array(
			ValueFormatter::OPT_LANG => $this->languageCode,
			JsonLdEntityFormatter::OPT_ENTITY_BASE_URI => 'http://www.wikidata.org/entity/'
		));
	}

	private function newGlobeCoordinateFormatter(FormatterOptions $options) {
		return new GlobeCoordinateFormatter(
			new JsonLdGlobeCoordinateFormatter(new \DataValues\Geo\Formatters\GlobeCoordinateFormatter($options), $options),
			$this->newJsonLdEntityIdFormatter($options),
			$options
		);
	}

	private function newQuantityFormatter(FormatterOptions $options) {
		return new JsonLdResourceFormatter(
			new JsonLdQuantityFormatter(
				new QuantityFormatter(new DecimalFormatter($options), $options),
				new JsonLdDecimalFormatter($options),
				$options
			),
			$options
		);
	}

	private function newEntityIdValueFormatter(FormatterOptions $options) {
		return new JsonLdResourceFormatter($this->newJsonLdEntityIdFormatter($options), $options);
	}

	private function newJsonLdEntityIdFormatter(FormatterOptions $options) {
		$entityFormatter = new JsonLdEntityFormatter($options);

		return new JsonLdEntityIdFormatter(
			$this->entityStore->getItemLookup(),
			new ExtendedJsonLdItemFormatter(
				new JsonLdItemFormatter($entityFormatter, $options),
				$this->newMediawikiArticleHeaderProvider(),
				$this->newMediawikiArticleImageProvider(),
				$options
			),
			$this->entityStore->getPropertyLookup(),
			new JsonLdPropertyFormatter($entityFormatter, $options),
			$options
		);
	}

	public function newWikibaseEntityIdFormatterPreloader() {
		return new WikibaseEntityIdFormatterPreloader(
			$this->entityStore,
			array(
				$this->newMediawikiArticleHeaderProvider(),
				$this->newMediawikiArticleImageProvider()
			),
			$this->languageCode
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
