<?php

namespace PPP\Wikidata\ValueFormatters;

use PPP\DataModel\JsonLdResourceNode;
use ValueFormatters\FormatterOptions;
use ValueFormatters\Test\ValueFormatterTestBase;
use ValueFormatters\ValueFormatter;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\EntityStore\InMemory\InMemoryEntityStore;

/**
 * @covers PPP\Wikidata\ValueFormatters\WikibaseEntityIdFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseEntityIdFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new EntityIdValue(new ItemId('Q42')),
				new JsonLdResourceNode(
					'Douglas Adams',
					(object) array('@context' => 'http://schema.org')
				),
				new FormatterOptions(array(ValueFormatter::OPT_LANG => 'en'))
			),
			array(
				new EntityIdValue(new ItemId('Q42')),
				new JsonLdResourceNode(
					'',
					(object) array('@context' => 'http://schema.org')
				),
				new FormatterOptions(array(ValueFormatter::OPT_LANG => 'de'))
			),
			array(
				new EntityIdValue(new PropertyId('P214')),
				new JsonLdResourceNode(
					'VIAF identifier',
					(object) array('@context' => 'http://schema.org')
				),
			)
		);
	}

	/**
	 * @see ValueFormatterTestBase::getFormatterClass
	 */
	protected function getFormatterClass() {
		return 'PPP\Wikidata\ValueFormatters\WikibaseEntityIdFormatter';
	}


	/**
	 * @see ValueFormatterTestBase::getInstance
	 */
	protected function getInstance(FormatterOptions $options) {
		$class = $this->getFormatterClass();

		$entityJsonLdFormatterMock = $this->getMockBuilder('PPP\Wikidata\ValueFormatters\WikibaseEntityIdJsonLdFormatter')
			->disableOriginalConstructor()
			->getMock();
		$entityJsonLdFormatterMock->expects($this->once())
			->method('format')
			->will($this->returnValue((object) array('@context' => 'http://schema.org')));

		return new $class(
			new InMemoryEntityStore(array($this->getQ42(), $this->getP214())),
			$entityJsonLdFormatterMock,
			$options
		);
	}

	private function getQ42() {
		$item = new Item();
		$item->setId( new ItemId('Q42'));
		$item->getFingerprint()->setLabel('en', 'Douglas Adams');

		return $item;
	}

	private function getP214() {
		$property = Property::newFromType('string');
		$property->setId(new PropertyId('P214'));
		$property->getFingerprint()->setLabel('en', 'VIAF identifier');

		return $property;
	}
}
