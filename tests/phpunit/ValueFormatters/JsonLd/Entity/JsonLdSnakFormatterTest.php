<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd\Entity;

use DataValues\StringValue;
use ValueFormatters\FormatterOptions;
use ValueFormatters\Test\ValueFormatterTestBase;
use Wikibase\DataModel\Claim\Claim;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\PropertyLookup;
use Wikibase\DataModel\Entity\PropertyNotFoundException;
use Wikibase\DataModel\Snak\PropertyNoValueSnak;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdSnakFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdSnakFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		$withPropertyLookupMock = $this->getMock('Wikibase\DataModel\Entity\PropertyLookup');
		$withPropertyLookupMock->expects($this->once())
			->method('getPropertyForId')
			->with($this->equalTo(new PropertyId('P21')))
			->willReturn($this->getP21());

		$withoutPropertyLookupMock = $this->getMock('Wikibase\DataModel\Entity\PropertyLookup');
		$withoutPropertyLookupMock->expects($this->once())
			->method('getPropertyForId')
			->with($this->equalTo(new PropertyId('P21')))
			->willThrowException(new PropertyNotFoundException(new PropertyId('P21')));

		return array(
			array(
				new PropertyValueSnak(new PropertyId('P21'), new EntityIdValue(new ItemId('Q1'))),
				array(
					'http://schema.org/gender' => (object) array('name' => 'test')
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
	 * @see ValueFormatterTestBase::getFormatterClass
	 */
	protected function getFormatterClass() {
		return 'PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdSnakFormatter';
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
				new Statement(new Claim(new PropertyValueSnak(new PropertyId('P1628'), new StringValue('http://schema.org/gender')))),
				new Statement(new Claim(new PropertyValueSnak(new PropertyId('P1628'), new StringValue('http://xmlns.com/foaf/0.1/gender'))))
			))
		);
	}
}
