<?php

namespace PPP\Wikidata\ValueFormatters;

use Doctrine\Common\Cache\Cache;
use Mediawiki\Api\MediawikiApi;
use PPP\Wikidata\Cache\JsonLdDataValueFormatterCache;
use PPP\Wikidata\Cache\PerSiteLinkCache;
use PPP\Wikidata\ValueFormatters\JsonLd\DispatchingJsonLdDataValueFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\Entity\EntityOntology;
use PPP\Wikidata\ValueFormatters\JsonLd\Entity\ExtendedJsonLdEntityFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdEntityFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdEntityIdFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdItemFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdPropertyFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdSnakFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdDataValueFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdDecimalFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdGlobeCoordinateFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdMonolingualTextFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdQuantityFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdStringFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdTimeFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdUnknownFormatter;
use PPP\Wikidata\Wikipedia\MediawikiArticleProvider;
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

		$dispatchingFormatter = $this->newExtendedDispatchingJsonLdDataValueFormatter($options);
		return new JsonLdResourceFormatter(
			$dispatchingFormatter,
			$this->newSnakFormatter($dispatchingFormatter, $options),
			$options
		);
	}

	private function newFormatterOptions() {
		return new FormatterOptions(array(
			ValueFormatter::OPT_LANG => $this->languageCode,
			JsonLdEntityFormatter::OPT_ENTITY_BASE_URI => 'http://www.wikidata.org/entity/',
			JsonLdSnakFormatter::OPT_ALLOWED_VOCABULARIES => array('http://schema.org/')
		));
	}

	private function newJsonLdGlobeCoordinateFormatter(FormatterOptions $options) {
		return new JsonLdGlobeCoordinateFormatter(new \DataValues\Geo\Formatters\GlobeCoordinateFormatter($options), $options);
	}

	private function newJsonLdQuantityFormatter(FormatterOptions $options) {
		return new JsonLdQuantityFormatter(
			new QuantityFormatter($options, new DecimalFormatter($options)),
			new JsonLdDecimalFormatter($options),
			$options
		);
	}

	private function newSimpleJsonLdEntityIdFormatter(FormatterOptions $options) {
		$entityFormatter = new JsonLdEntityFormatter($options);

		return new CachedJsonLdDataValueFormatter(
			new JsonLdEntityIdFormatter(
				$this->entityStore->getItemLookup(),
				new JsonLdItemFormatter($entityFormatter, $options),
				$this->entityStore->getPropertyLookup(),
				new JsonLdPropertyFormatter($entityFormatter, $options),
				$options
			),
			new JsonLdDataValueFormatterCache(
				$this->cache,
				'simpleentityid'
			)
		);
	}

	private function newExtendedJsonLdEntityIdFormatter(FormatterOptions $options) {
		$entityFormatter = new ExtendedJsonLdEntityFormatter(
			new JsonLdEntityFormatter($options),
			$this->newSnakFormatter($this->newSimpleDispatchingJsonLdDataValueFormatter($options), $options),
			$options
		);

		return new CachedJsonLdDataValueFormatter(
			new JsonLdEntityIdFormatter(
				$this->entityStore->getItemLookup(),
				new ExtendedJsonLdItemFormatter(
					new JsonLdItemFormatter($entityFormatter, $options),
					$this->newMediawikiArticleProvider(),
					$options
				),
				$this->entityStore->getPropertyLookup(),
				new JsonLdPropertyFormatter($entityFormatter, $options),
				$options
			),
			new JsonLdDataValueFormatterCache(
				$this->cache,
				'extendedentityid'
			)
		);
	}

	private function newSnakFormatter(JsonLdDataValueFormatter $dataValueFormatter, FormatterOptions $options) {
		return new JsonLdSnakFormatter(
			$this->entityStore->getPropertyLookup(),
			new EntityOntology(array(
				EntityOntology::OWL_EQUIVALENT_PROPERTY => new PropertyId('P1628')
			)),
			$dataValueFormatter,
			$options
		);
	}

	private function newSimpleDispatchingJsonLdDataValueFormatter(FormatterOptions $options) {
		return new DispatchingJsonLdDataValueFormatter(array(
			'globecoordinate' => $this->newJsonLdGlobeCoordinateFormatter($options),
			'monolingualtext' => new JsonLdMonolingualTextFormatter($options),
			'quantity' => $this->newJsonLdQuantityFormatter($options),
			'string' => new JsonLdStringFormatter($options),
			'time' => new JsonLdTimeFormatter(new IsoTimeFormatter($options), $options),
			'unknown' => new JsonLdUnknownFormatter($options),
			'wikibase-entityid' => $this->newSimpleJsonLdEntityIdFormatter($options)
		), $options);
	}

	private function newExtendedDispatchingJsonLdDataValueFormatter(FormatterOptions $options) {
		return new DispatchingJsonLdDataValueFormatter(array(
			'globecoordinate' => $this->newJsonLdGlobeCoordinateFormatter($options),
			'monolingualtext' => new JsonLdMonolingualTextFormatter($options),
			'quantity' => $this->newJsonLdQuantityFormatter($options),
			'string' => new JsonLdStringFormatter($options),
			'time' => new JsonLdTimeFormatter(new IsoTimeFormatter($options), $options),
			'unknown' => new JsonLdUnknownFormatter($options),
			'wikibase-entityid' => $this->newExtendedJsonLdEntityIdFormatter($options)
		), $options);
	}

	private function newMediawikiArticleProvider() {
		return new MediawikiArticleProvider(
			$this->sitesApi,
			new PerSiteLinkCache($this->cache, 'wparticle')
		);
	}
}
