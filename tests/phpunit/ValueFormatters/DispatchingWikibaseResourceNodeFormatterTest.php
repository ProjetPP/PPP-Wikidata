<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\StringValue;
use PPP\DataModel\StringResourceNode;
use PPP\Wikidata\WikibaseResourceNode;
use ValueFormatters\Test\ValueFormatterTestBase;

/**
 * @covers PPP\Wikidata\ValueFormatters\DispatchingWikibaseResourceNodeFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class DispatchingWikibaseResourceNodeFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		$formatterMock = $this->getMock('ValueFormatters\ValueFormatter');
		$formatterMock->expects($this->any())
			->method('format')
			->with($this->equalTo(new WikibaseResourceNode('', new StringValue('foo'))))
			->will($this->returnValue(new StringResourceNode('foo')));

		return array(
			array(
				new WikibaseResourceNode('', new StringValue('foo')),
				new StringResourceNode('foo'),
				null,
				$formatterMock
			),
		);
	}

	/**
	 * @see ValueFormatterTestBase::getFormatterClass
	 */
	protected function getFormatterClass() {
		return 'PPP\Wikidata\ValueFormatters\WikibaseValueFormatter';
	}
}
