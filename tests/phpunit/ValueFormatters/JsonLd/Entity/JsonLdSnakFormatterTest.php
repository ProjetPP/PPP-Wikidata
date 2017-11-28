<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd\Entity;

use DataValues\StringValue;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdFormatterTestBase;
use ValueFormatters\FormatterOptions;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\Lookup\PropertyLookup;
use Wikibase\DataModel\Snak\PropertyNoValueSnak;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdSnakFormatter
 *
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class JsonLdSnakFormatterTest extends JsonLdFormatterTestBase {

	/**
	 * @see JsonLdFormatterTestBase::validProvider
	 */
	public function validProvider() {
		$withPropertyLookupMock = $this->getMock('Wikibase\DataModel\Services\Lookup\PropertyLookup');
		$withPropertyLookupMock->expects($this->once())
			->method('getPropertyForId')
			->with($this->equalTo(new PropertyId('P21')))
			->willReturn($this->getP21());

		$withoutPropertyLookupMock = $this->getMock('Wikibase\DataModel\Services\Lookup\PropertyLookup');
		$withoutPropertyLookupMock->expects($this->once())
			->method('getPropertyForId')
			->with($this->equalTo(new PropertyId('P21')))
			->willReturn(null);

		return array(
			array(
				new PropertyValueSnak(new PropertyId('P21'), new EntityIdValue(new ItemId('Q1'))),
				array(
					'gender' => (object) array('name' => 'test')
				),
				null,
				$this->getFormatter($withPropertyLookupMock)
			),
			array(
				new PropertyValueSnak(new PropertyId('P21'), new EntityIdValue(new ItemId('Q1'))),
				array(),
				null,
				$this->getFormatter($withoutPropertyLookupMock)
			),
			array(
				new PropertyNoValueSnak(new PropertyId('P21')),
				array(),
				null,
				$this->getFormatter($withPropertyLookupMock)
			),
		);
	}

	/**
	 * @see JsonLdFormatterTestBase::getInstance
	 */
	protected function getInstance(FormatterOptions $options = null) {
		return null;
	}

	private function getFormatter(PropertyLookup $propertyLookup) {
		$dataValueformatterMock = $this->getMock('PPP\Wikidata\ValueFormatters\JsonLd\JsonLdDataValueFormatter');
		$dataValueformatterMock->expects($this->once())
			->method('format')
			->with($this->equalTo(new EntityIdValue(new ItemId('Q1'))))
			->willReturn((object) array('name' => 'test'));

		$options = new FormatterOptions(array(
			JsonLdSnakFormatter::OPT_ALLOWED_VOCABULARIES => array('http://schema.org/')
		));

		return new JsonLdSnakFormatter(
			$propertyLookup,
			new EntityOntology(array(
				EntityOntology::OWL_EQUIVALENT_PROPERTY => new PropertyId('P1628')
			)),
			$dataValueformatterMock,
			$options
		);
	}

	private function getP21() {
		return new Property(
			new PropertyId('P21'),
			null,
			'string',
			new StatementList(array(
				new Statement(new PropertyValueSnak(new PropertyId('P1628'), new StringValue('http://schema.org/gender'))),
				new Statement(new PropertyValueSnak(new PropertyId('P1628'), new StringValue('http://xmlns.com/foaf/0.1/gender')))
			))
		);
	}
}
