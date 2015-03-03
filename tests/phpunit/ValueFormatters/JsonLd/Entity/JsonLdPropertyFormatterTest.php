<?php

namespace PPP\Wikidata\ValueFormatters;

use PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdEntityFormatter;
use ValueFormatters\FormatterOptions;
use ValueFormatters\Test\ValueFormatterTestBase;
use ValueFormatters\ValueFormatter;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdPropertyFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdPropertyFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new Property(new PropertyId('P214'), null, 'string'),
				(object) array(
					'@type' => 'Property',
					'@id' => 'http://www.wikidata.org/entity/P214',
					'name' => 'P214',
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
		return 'PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdPropertyFormatter';
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
