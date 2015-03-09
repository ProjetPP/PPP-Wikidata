<?php

namespace PPP\Wikidata\ValueFormatters;

use Doctrine\Common\Cache\Cache;
use Mediawiki\Api\MediawikiApi;
use PPP\Wikidata\Cache\PerSiteLinkCache;
use PPP\Wikidata\ValueFormatters\JsonLd\DispatchingJsonLdDataValueFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\Entity\EntityOntology;
use PPP\Wikidata\ValueFormatters\JsonLd\Entity\ExtendedJsonLdEntityFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdEntityFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdEntityIdFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdItemFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdPropertyFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdSnakFormatter;
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
use Wikibase\DataModel\Entity\PropertyId;
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
			'quantity' => new JsonLdResourceFormatter($this->newJsonLdQuantityFormatter($options), $options),
			'string' => new JsonLdLiteralFormatter(new JsonLdStringFormatter($options), $options),
			'time' => new JsonLdLiteralFormatter(new JsonLdTimeFormatter(new IsoTimeFormatter($options), $options), $options),
			'unknown' => new JsonLdLiteralFormatter(new JsonLdUnknownFormatter($options), $options),
			'wikibase-entityid' => new JsonLdResourceFormatter($this->newExtendedJsonLdEntityIdFormatter($options), $options)
		));
	}

	private function newFormatterOptions() {
		return new FormatterOptions(array(
			ValueFormatter::OPT_LANG => $this->languageCode,
			JsonLdEntityFormatter::OPT_ENTITY_BASE_URI => 'http://www.wikidata.org/entity/',
			JsonLdSnakFormatter::OPT_ALLOWED_VOCABULARIES => array('http://schema.org/')
		));
	}

	private function newGlobeCoordinateFormatter(FormatterOptions $options) {
		return new GlobeCoordinateFormatter(
			new JsonLdGlobeCoordinateFormatter(new \DataValues\Geo\Formatters\GlobeCoordinateFormatter($options), $options),
			$this->newExtendedJsonLdEntityIdFormatter($options),
			$options
		);
	}

	private function newJsonLdQuantityFormatter(FormatterOptions $options) {
		return new JsonLdQuantityFormatter(
			new QuantityFormatter(new DecimalFormatter($options), $options),
			new JsonLdDecimalFormatter($options),
			$options
		);
	}

	private function newSimpleJsonLdEntityIdFormatter(FormatterOptions $options) {
		$entityFormatter = new JsonLdEntityFormatter($options);

		return new JsonLdEntityIdFormatter(
			$this->entityStore->getItemLookup(),
			new JsonLdItemFormatter($entityFormatter, $options),
			$this->entityStore->getPropertyLookup(),
			new JsonLdPropertyFormatter($entityFormatter, $options),
			$options
		);
	}

	private function newExtendedJsonLdEntityIdFormatter(FormatterOptions $options) {
		$entityFormatter = new ExtendedJsonLdEntityFormatter(
			new JsonLdEntityFormatter($options),
			new JsonLdSnakFormatter(
				$this->entityStore->getPropertyLookup(),
				new EntityOntology(array(
					EntityOntology::OWL_EQUIVALENT_PROPERTY => new PropertyId('P1628')
				)),
				$this->newDispatchingJsonLdDataValueFormatter($options),
				$options
			), $options);

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

	private function newDispatchingJsonLdDataValueFormatter(FormatterOptions $options) {
		return new DispatchingJsonLdDataValueFormatter(array(
			'globecoordinate' => new JsonLdGlobeCoordinateFormatter(
				new \DataValues\Geo\Formatters\GlobeCoordinateFormatter($options),
				$options
			),
			'monolingualtext' => new JsonLdMonolingualTextFormatter($options),
			'quantity' => $this->newJsonLdQuantityFormatter($options),
			'string' => new JsonLdStringFormatter($options),
			'time' => new JsonLdTimeFormatter(new IsoTimeFormatter($options), $options),
			'unknown' => new JsonLdUnknownFormatter($options),
			'wikibase-entityid' => $this->newSimpleJsonLdEntityIdFormatter($options)
		), $options);
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
