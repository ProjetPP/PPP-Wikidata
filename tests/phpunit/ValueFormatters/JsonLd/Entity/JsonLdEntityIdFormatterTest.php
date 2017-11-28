<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd\Entity;

use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdFormatterTestBase;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\Lookup\ItemLookup;
use Wikibase\DataModel\Services\Lookup\PropertyLookup;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\Term;
use Wikibase\DataModel\Term\TermList;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdEntityIdFormatter
 *
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class JsonLdEntityIdFormatterTest extends JsonLdFormatterTestBase {

	/**
	 * @see JsonLdFormatterTestBase::validProvider
	 */
	public function validProvider() {
		$withItemLookupMock = $this->getMock('Wikibase\DataModel\Services\Lookup\ItemLookup');
		$withItemLookupMock->expects($this->once())
			->method('getItemForId')
			->with($this->equalTo(new ItemId('Q42')))
			->willReturn(new Item(
				new ItemId('Q42'),
				new Fingerprint(new TermList(array(new Term('en', 'Douglas Adams'))))
			));
		$withoutItemLookupMock = $this->getMock('Wikibase\DataModel\Services\Lookup\ItemLookup');
		$withoutItemLookupMock->expects($this->once())
			->method('getItemForId')
			->with($this->equalTo(new ItemId('Q42')))
			->willReturn(null);

		$withPropertyLookupMock = $this->getMock('Wikibase\DataModel\Services\Lookup\PropertyLookup');
		$withPropertyLookupMock->expects($this->once())
			->method('getPropertyForId')
			->with($this->equalTo(new PropertyId('P214')))
			->willReturn(new Property(
				new PropertyId('P214'),
				new Fingerprint(new TermList(array(new Term('en', 'VIAF')))),
				'string'
			));
		$withoutPropertyLookupMock = $this->getMock('Wikibase\DataModel\Services\Lookup\PropertyLookup');
		$withoutPropertyLookupMock->expects($this->once())
			->method('getPropertyForId')
			->with($this->equalTo(new PropertyId('P214')))
			->willReturn(null);

		return array(
			array(
				new EntityIdValue(new ItemId('Q42')),
				(object) array(
					'@type' => 'Thing',
					'@id' => 'http://www.wikidata.org/entity/Q42',
					'name' => (object) array('@value' => 'Douglas Adams', '@language' => 'en')
				),
				null,
				$this->getFormatter(
					$withItemLookupMock,
					$this->getMock('Wikibase\DataModel\Services\Lookup\PropertyLookup')
				)
			),
			array(
				new EntityIdValue(new ItemId('Q42')),
				(object) array(
					'@type' => 'Thing',
					'@id' => 'http://www.wikidata.org/entity/Q42',
					'name' => 'Q42'
				),
				null,
				$this->getFormatter(
					$withoutItemLookupMock,
					$this->getMock('Wikibase\DataModel\Services\Lookup\PropertyLookup')
				)
			),
			array(
				new EntityIdValue(new PropertyId('P214')),
				(object) array(
					'@type' => 'Property',
					'@id' => 'http://www.wikidata.org/entity/P214',
					'name' => (object) array('@value' => 'VIAF', '@language' => 'en')
				),
				null,
				$this->getFormatter(
					$this->getMock('Wikibase\DataModel\Services\Lookup\ItemLookup'),
					$withPropertyLookupMock
				)
			),
			array(
				new EntityIdValue(new PropertyId('P214')),
				(object) array(
					'@type' => 'Property',
					'@id' => 'http://www.wikidata.org/entity/P214',
					'name' => 'P214'
				),
				null,
				$this->getFormatter(
					$this->getMock('Wikibase\DataModel\Services\Lookup\ItemLookup'),
					$withoutPropertyLookupMock
				)
			)
		);
	}

	/**
	 * @see JsonLdFormatterTestBase::getInstance
	 */
	protected function getInstance(FormatterOptions $options = null) {
		return null;
	}

	private function getFormatter(ItemLookup $itemLookup, PropertyLookup $propertyLookup) {
		$options = new FormatterOptions(array(
			ValueFormatter::OPT_LANG => 'en',
			JsonLdEntityFormatter::OPT_ENTITY_BASE_URI => 'http://www.wikidata.org/entity/'
		));

		return new JsonLdEntityIdFormatter(
			$itemLookup,
			new JsonLdItemFormatter(new JsonLdEntityFormatter($options), $options),
			$propertyLookup,
			new JsonLdPropertyFormatter(new JsonLdEntityFormatter($options), $options),
			$options
		);
	}
}
