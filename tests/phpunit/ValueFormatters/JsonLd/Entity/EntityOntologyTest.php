<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\StringValue;
use PPP\Wikidata\ValueFormatters\JsonLd\Entity\EntityOntology;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLd\Entity\EntityOntology
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class EntityOntologyTest extends \PHPUnit_Framework_TestCase {

	public function testGetEquivalentPropertiesIris() {
		$ontology = new EntityOntology(array(
			EntityOntology::OWL_EQUIVALENT_PROPERTY => new PropertyId('P1628')
		));

		$this->assertEquals(array('http://schema.org/gender'), $ontology->getEquivalentPropertiesIris($this->getProperty()));
	}

	public function testGetEquivalentPropertiesIrisWithoutConfig() {
		$ontology = new EntityOntology(array());

		$this->assertEquals(array(), $ontology->getEquivalentPropertiesIris($this->getProperty()));
	}

	private function getProperty() {
		return new Property(
			new PropertyId('P214'),
			null,
			'string',
			new StatementList(array(
				new Statement(new PropertyValueSnak(new PropertyId('P1628'), new StringValue('http://schema.org/gender')))
			))
		);
	}

	public function testGetUnitSymbol() {
		$ontology = new EntityOntology(array(
			EntityOntology::QUDT_SYMBOL => new PropertyId('P558')
		));

		$this->assertEquals('m', $ontology->getUnitSymbol($this->getItem()));
	}

	public function testGetUnitSymbolWithException() {
		$ontology = new EntityOntology(array());

		$this->setExpectedException('OutOfBoundsException');
		$ontology->getUnitSymbol($this->getItem());
	}

	private function getItem() {
		$deprecatedStatement = new Statement(new PropertyValueSnak(new PropertyId('P558'), new StringValue('p')));
		$deprecatedStatement->setRank(Statement::RANK_DEPRECATED);
		return new Item(
			new ItemId('Q1'),
			null,
			null,
			new StatementList(array(
				new Statement(new PropertyValueSnak(new PropertyId('P558'), new StringValue('m'))),
				$deprecatedStatement
			))
		);
	}
}