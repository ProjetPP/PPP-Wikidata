<?php

namespace PPP\Wikidata\ValueFormatters;

use PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdEntityFormatter;
use ValueFormatters\FormatterOptions;
use ValueFormatters\Test\ValueFormatterTestBase;
use ValueFormatters\ValueFormatter;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdItemFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdItemFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new Item(new ItemId('Q42')),
				(object) array(
					'@type' => 'Thing',
					'@id' => 'http://www.wikidata.org/entity/Q42',
					'name' => 'Q42',
					'@reverse' => (object) array()
				),
				new FormatterOptions(array(
					ValueFormatter::OPT_LANG => 'en',
					JsonLdEntityFormatter::OPT_ENTITY_BASE_URI => 'http://www.wikidata.org/entity/'
				))
			)
		);
	}

	/**
	 * @see ValueFormatterTestBase::getFormatterClass
	 */
	protected function getFormatterClass() {
		return 'PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdItemFormatter';
	}

	/**
	 * @see ValueFormatterTestBase::getInstance
	 */
	protected function getInstance(FormatterOptions $options) {
		$class = $this->getFormatterClass();

		return new $class(
			new JsonLdEntityFormatter($options),
			$options
		);
	}
}
