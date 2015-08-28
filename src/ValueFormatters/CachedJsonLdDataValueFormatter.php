<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\DataValue;
use InvalidArgumentException;
use OutOfBoundsException;
use PPP\Wikidata\Cache\JsonLdDataValueFormatterCache;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdDataValueFormatter;

/**
 * Adds a cache level on top of JsonLdDataValueFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class CachedJsonLdDataValueFormatter implements JsonLdDataValueFormatter {

	/**
	 * @var JsonLdDataValueFormatter
	 */
	private $formatter;

	/**
	 * @var JsonLdDataValueFormatterCache
	 */
	private $cache;


	public function __construct(JsonLdDataValueFormatter $formatter, JsonLdDataValueFormatterCache $cache) {
		$this->formatter = $formatter;
		$this->cache = $cache;
	}

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof DataValue)) {
			throw new InvalidArgumentException('$value is not a DataValue.');
		}

		try {
			return $this->cache->fetch($value);
		} catch(OutOfBoundsException $e) {
			$jsonLd = $this->formatter->format($value);
			$this->cache->save($value, $jsonLd);
			return $jsonLd;
		}
	}
}
