<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\Geo\Values\LatLongValue;
use DataValues\GlobeCoordinateValue;
use PPP\DataModel\JsonLdResourceNode;
use PPP\Wikidata\WikibaseResourceNode;
use ValueFormatters\FormatterOptions;
use ValueFormatters\Test\ValueFormatterTestBase;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers PPP\Wikidata\ValueFormatters\GlobeCoordinateFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class GlobeCoordinateFormatterTest extends ValueFormatterTestBase {

	/**T
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new WikibaseResourceNode('', new GlobeCoordinateValue(new LatLongValue(42, 42), 1)),
				new JsonLdResourceNode(
					'42, 42',
					(object) array(
						'@context' => 'http://schema.org',
                        '@type' => 'GeoCoordinates',
                        'latitude' => 42.0,
						'longitude' => 42.0
					)
				)
			),
			array(
				new WikibaseResourceNode('', new GlobeCoordinateValue(new LatLongValue(42, 42), 1), new ItemId('Q42')),
				new JsonLdResourceNode(
					'42, 42',
					(object) array(
						'@context' => 'http://schema.org',
						'@type' => 'GeoCoordinates',
						'latitude' => 42.0,
						'longitude' => 42.0,
						'@reverse' => (object) array(
							'geo' => (object) array(
								'@id' => 'http://exemple.org'
							)
						)
					)
				)
			),
		);
	}

	/**
	 * @see ValueFormatterTestBase::getFormatterClass
	 *
	 * @return string
	 */
	protected function getFormatterClass() {
		return 'PPP\Wikidata\ValueFormatters\GlobeCoordinateFormatter';
	}

	/**
	 * @see ValueFormatterTestBase::getInstance
	 */
	protected function getInstance(FormatterOptions $options) {
		$class = $this->getFormatterClass();

		$entityJsonLdFormatterMock = $this->getMockBuilder('PPP\Wikidata\ValueFormatters\WikibaseEntityIdJsonLdFormatter')
			->disableOriginalConstructor()
			->getMock();
		$entityJsonLdFormatterMock->expects($this->any())
			->method('format')
			->with($this->equalTo(new ItemId('Q42')))
			->will($this->returnValue((object) array('@id' => 'http://exemple.org')));

		return new $class($entityJsonLdFormatterMock, $options);
	}
}