<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\MonolingualTextValue;
use DataValues\TimeValue;
use PPP\DataModel\JsonLdResourceNode;
use PPP\Wikidata\WikibaseResourceNode;
use ValueFormatters\FormatterOptions;
use ValueFormatters\Test\ValueFormatterTestBase;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLdResourceFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdResourceFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		$withNameArrayFormatter = $this->getMock('PPP\Wikidata\ValueFormatters\JsonLd\JsonLdDataValueFormatter');
		$withNameArrayFormatter->expects($this->once())
			->method('format')
			->with($this->equalTo(new EntityIdValue(new ItemId('Q1'))))
			->will($this->returnValue((object) array('@type' => 'Thing', 'name' => array('foo'))));

		$withNameObjectFormatter = $this->getMock('PPP\Wikidata\ValueFormatters\JsonLd\JsonLdDataValueFormatter');
		$withNameObjectFormatter->expects($this->once())
			->method('format')
			->with($this->equalTo(new EntityIdValue(new ItemId('Q1'))))
			->will($this->returnValue((object) array(
				'@type' => 'Thing',
				'name' => (object) array('@language' => 'en', '@value' => 'foo'),
				'@reverse' => (object) array()
			)));

		$withOtherLanguageNameFormatter = $this->getMock('PPP\Wikidata\ValueFormatters\JsonLd\JsonLdDataValueFormatter');
		$withOtherLanguageNameFormatter->expects($this->once())
			->method('format')
			->with($this->equalTo(new EntityIdValue(new ItemId('Q1'))))
			->will($this->returnValue((object) array(
				'@type' => 'Thing',
				'name' => (object) array('@language' => 'fr', '@value' => 'foo')
			)));

		$snakFormatterMock = $this->getMock('ValueFormatters\ValueFormatter');
		$snakFormatterMock->expects($this->any())
			->method('format')
			->with($this->equalTo(new PropertyValueSnak(new PropertyId('P21'), new EntityIdValue(new ItemId('Q1')))))
			->willReturn(array(
				'http://schema.org/gender' => (object) array('name' => 'foo')
			));


		$snakEmptyFormatterMock = $this->getMock('ValueFormatters\ValueFormatter');
		$snakEmptyFormatterMock->expects($this->any())
			->method('format')
			->with($this->equalTo(new PropertyValueSnak(new PropertyId('P21'), new EntityIdValue(new ItemId('Q1')))))
			->willReturn(array());

		$withTypeLiteralFormatter = $this->getMock('PPP\Wikidata\ValueFormatters\JsonLd\JsonLdDataValueFormatter');
		$withTypeLiteralFormatter->expects($this->once())
			->method('format')
			->with($this->equalTo(new TimeValue('+00000001952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, '')))
			->will($this->returnValue((object) array( '@type' => 'Date', '@value' => '1952-03-11')));

		$withoutTypeLiteralFormatter = $this->getMock('PPP\Wikidata\ValueFormatters\JsonLd\JsonLdDataValueFormatter');
		$withoutTypeLiteralFormatter->expects($this->once())
			->method('format')
			->with($this->equalTo(new MonolingualTextValue('en', 'foo')))
			->will($this->returnValue((object) array( '@language' => 'en', '@value' => 'foo')));

		return array(
			array(
				new WikibaseResourceNode(
					'',
					new EntityIdValue(new ItemId('Q1')),
					new ItemId('Q1'),
					new PropertyId('P21')
				),
				new JsonLdResourceNode(
					'foo',
					(object) array(
						'@context' => 'http://schema.org',
						'@type' => 'Thing',
						'name' => array('foo'),
						'@reverse' => (object) array(
							'http://schema.org/gender' => array((object) array('name' => 'foo'))
						)
					)
				),
				null,
				new JsonLdResourceFormatter($withNameArrayFormatter, $snakFormatterMock, new FormatterOptions())
			),
			array(
				new WikibaseResourceNode(
					'',
					new EntityIdValue(new ItemId('Q1')),
					new ItemId('Q1'),
					new PropertyId('P21')
				),
				new JsonLdResourceNode(
					'foo',
					(object) array(
						'@context' => 'http://schema.org',
						'@type' => 'Thing',
						'name' => (object) array('@language' => 'en', '@value' => 'foo'),
						'@reverse' => (object) array(
							'http://schema.org/gender' => array((object) array('name' => 'foo'))
						)
					)
				),
				null,
				new JsonLdResourceFormatter($withNameObjectFormatter, $snakFormatterMock, new FormatterOptions())
			),
			array(
				new WikibaseResourceNode(
					'',
					new EntityIdValue(new ItemId('Q1'))
				),
				new JsonLdResourceNode(
					'foo',
					(object) array(
						'@context' => 'http://schema.org',
						'@type' => 'Thing',
						'name' => (object) array('@language' => 'fr', '@value' => 'foo')
					)
				),
				null,
				new JsonLdResourceFormatter($withOtherLanguageNameFormatter, $snakFormatterMock, new FormatterOptions())
			),
			array(
				new WikibaseResourceNode(
					'',
					new TimeValue('+00000001952-03-11T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, '')
				),
				new JsonLdResourceNode(
					'1952-03-11',
					(object) array(
						'@context' => 'http://schema.org',
						'@type' => 'Date',
						'http://www.w3.org/1999/02/22-rdf-syntax-ns#value' => (object) array(
							'@type' => 'Date',
							'@value' => '1952-03-11'
						)
					)
				),
				null,
				new JsonLdResourceFormatter($withTypeLiteralFormatter, $snakFormatterMock, new FormatterOptions())
			),
			array(
				new WikibaseResourceNode(
					'',
					new MonolingualTextValue('en', 'foo'),
					new ItemId('Q1'),
					new PropertyId('P21')
				),
				new JsonLdResourceNode(
					'foo',
					(object) array(
						'@context' => 'http://schema.org',
						'@type' => 'Text',
						'http://www.w3.org/1999/02/22-rdf-syntax-ns#value' => (object) array(
							'@language' => 'en',
							'@value' => 'foo'
						)
					)
				),
				null,
				new JsonLdResourceFormatter($withoutTypeLiteralFormatter, $snakEmptyFormatterMock, new FormatterOptions())
			),
		);
	}

	/**
	 * @see ValueFormatterTestBase::getFormatterClass
	 *
	 * @return string
	 */
	protected function getFormatterClass() {
		return 'PPP\Wikidata\ValueFormatters\JsonLdResourceFormatter';
	}
}
