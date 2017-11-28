<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd\Entity;

use OutOfBoundsException;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdFormatterTestBase;
use ValueFormatters\FormatterOptions;
use Wikibase\DataModel\Entity\EntityIdParsingException;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\Term;
use Wikibase\DataModel\Term\TermList;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLd\Entity\UnitSymbolFormatter
 *
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class UnitSymbolFormatterTest extends JsonLdFormatterTestBase {

	/**
	 * @see JsonLdFormatterTestBase::validProvider
	 */
	public function validProvider() {
		$item = new Item(new ItemId('Q11573'), new Fingerprint(new TermList(array(new Term('en', 'meter')))));

		$entityIriParserMock = $this->getMock('Wikibase\DataModel\Entity\EntityIdParser');
		$entityIriParserMock->expects($this->any())
			->method('parse')
			->with($this->equalTo('http://www.wikidata.org/entity/Q11573'))
			->willReturn(new ItemId('Q11573'));

		$itemLookupMock = $this->getMock('Wikibase\DataModel\Services\Lookup\ItemLookup');
		$itemLookupMock->expects($this->any())
			->method('getItemForId')
			->with($this->equalTo(new ItemId('Q11573')))
			->willReturn($item);

		$entityOntologyMockSuccess = $this->getMockBuilder('PPP\Wikidata\ValueFormatters\JsonLd\Entity\EntityOntology')
			->disableOriginalConstructor()
			->getMock();
		$entityOntologyMockSuccess->expects($this->once())
			->method('getUnitSymbol')
			->with($this->equalTo($item))
			->willReturn('m');

		$entityOntologyMockFailure = $this->getMockBuilder('PPP\Wikidata\ValueFormatters\JsonLd\Entity\EntityOntology')
			->disableOriginalConstructor()
			->getMock();
		$entityOntologyMockFailure->expects($this->once())
			->method('getUnitSymbol')
			->with($this->equalTo($item))
			->willThrowException(new OutOfBoundsException());

		return array(
			array(
				'http://www.wikidata.org/entity/Q11573',
				'm',
				null,
				new UnitSymbolFormatter($entityIriParserMock, $entityOntologyMockSuccess, $itemLookupMock)
			),
			array(
				'http://www.wikidata.org/entity/Q11573',
				'meter',
				null,
				new UnitSymbolFormatter($entityIriParserMock, $entityOntologyMockFailure, $itemLookupMock)
			)
		);
	}

	public function testWithEntityIdParsingException() {
		$entityIriParserMock = $this->getMock('Wikibase\DataModel\Entity\EntityIdParser');
		$entityIriParserMock->expects($this->once())
			->method('parse')
			->with($this->equalTo('http://www.wikidata.org/entity/Q11573'))
			->willThrowException(new EntityIdParsingException());

		$itemLookupMock = $this->getMock('Wikibase\DataModel\Services\Lookup\ItemLookup');
		$entityOntologyMock = $this->getMockBuilder('PPP\Wikidata\ValueFormatters\JsonLd\Entity\EntityOntology')
			->disableOriginalConstructor()
			->getMock();
		$formatter = new UnitSymbolFormatter($entityIriParserMock, $entityOntologyMock, $itemLookupMock);

		$this->setExpectedException('ValueFormatters\FormattingException');
		$formatter->format('http://www.wikidata.org/entity/Q11573');
	}

	public function testWithItemNotFoundException() {
		$entityIriParserMock = $this->getMock('Wikibase\DataModel\Entity\EntityIdParser');
		$entityIriParserMock->expects($this->once())
			->method('parse')
			->with($this->equalTo('http://www.wikidata.org/entity/Q11573'))
			->willReturn(new ItemId('Q11573'));

		$itemLookupMock = $this->getMock('Wikibase\DataModel\Services\Lookup\ItemLookup');
		$itemLookupMock->expects($this->once())
			->method('getItemForId')
			->with($this->equalTo(new ItemId('Q11573')))
			->willReturn(null);

		$entityOntologyMock = $this->getMockBuilder('PPP\Wikidata\ValueFormatters\JsonLd\Entity\EntityOntology')
			->disableOriginalConstructor()
			->getMock();
		$formatter = new UnitSymbolFormatter($entityIriParserMock, $entityOntologyMock, $itemLookupMock);

		$this->setExpectedException('ValueFormatters\FormattingException');
		$formatter->format('http://www.wikidata.org/entity/Q11573');
	}

	public function testWithoutSymbolAndLabel() {
		$entityIriParserMock = $this->getMock('Wikibase\DataModel\Entity\EntityIdParser');
		$entityIriParserMock->expects($this->once())
			->method('parse')
			->with($this->equalTo('http://www.wikidata.org/entity/Q11573'))
			->willReturn(new ItemId('Q11573'));

		$itemLookupMock = $this->getMock('Wikibase\DataModel\Services\Lookup\ItemLookup');
		$itemLookupMock->expects($this->once())
			->method('getItemForId')
			->with($this->equalTo(new ItemId('Q11573')))
			->willReturn(new Item(new ItemId('Q11573')));

		$entityOntologyMock = $this->getMockBuilder('PPP\Wikidata\ValueFormatters\JsonLd\Entity\EntityOntology')
			->disableOriginalConstructor()
			->getMock();
		$entityOntologyMock->expects($this->once())
			->method('getUnitSymbol')
			->with($this->equalTo(new Item(new ItemId('Q11573'))))
			->willThrowException(new OutOfBoundsException());

		$formatter = new UnitSymbolFormatter($entityIriParserMock, $entityOntologyMock, $itemLookupMock);

		$this->setExpectedException('ValueFormatters\FormattingException');
		$formatter->format('http://www.wikidata.org/entity/Q11573');
	}

	/**
	 * @see JsonLdFormatterTestBase::getInstance
	 */
	protected function getInstance(FormatterOptions $options = null) {
		return null;
	}
}
