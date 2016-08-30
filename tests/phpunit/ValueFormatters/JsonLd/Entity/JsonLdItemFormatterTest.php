<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd\Entity;

use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdFormatterTestBase;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdItemFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdItemFormatterTest extends JsonLdFormatterTestBase {

	/**
	 * @see JsonLdFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new Item(new ItemId('Q42')),
				(object) array(
					'@type' => 'Thing',
					'@id' => 'http://www.wikidata.org/entity/Q42',
					'name' => 'Q42'
				),
				new FormatterOptions(array(
					ValueFormatter::OPT_LANG => 'en',
					JsonLdEntityFormatter::OPT_ENTITY_BASE_URI => 'http://www.wikidata.org/entity/'
				))
			)
		);
	}

	/**
	 * @see JsonLdFormatterTestBase::getInstance
	 */
	protected function getInstance(FormatterOptions $options = null) {
		return new JsonLdItemFormatter(
			new JsonLdEntityFormatter($options),
			$options
		);
	}
}
