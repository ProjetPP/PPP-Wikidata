<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd\Entity;

use OutOfBoundsException;
use ValueFormatters\FormatterOptions;
use ValueFormatters\Test\ValueFormatterTestBase;
use Wikibase\DataModel\Entity\EntityIdParsingException;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLd\Entity\UnitSymbolFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class UnitSymbolFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
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
			->willReturn('m');

		$formatter = new UnitSymbolFormatter($entityIriParserMock, $entityOntologyMock, $itemLookupMock);

		return array(
			array(
				'http://www.wikidata.org/entity/Q11573',
				'm',
				null,
				$formatter
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

	public function testWithOutOfBoundsException() {
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
	 * @see ValueFormatterTestBase::getInstance
	 */
	protected function getInstance(FormatterOptions $options = null) {
		return null;
	}
}
