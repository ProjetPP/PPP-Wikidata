<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd\Entity;

use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdFormatterTestBase;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdPropertyFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdPropertyFormatterTest extends JsonLdFormatterTestBase {

	/**
	 * @see JsonLdFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new Property(new PropertyId('P214'), null, 'string'),
				(object) array(
					'@type' => 'Property',
					'@id' => 'http://www.wikidata.org/entity/P214',
					'name' => 'P214'
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
		return new JsonLdPropertyFormatter(
			new JsonLdEntityFormatter($options),
			$options
		);
	}
}
