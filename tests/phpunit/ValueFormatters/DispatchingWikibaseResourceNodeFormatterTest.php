<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\StringValue;
use PPP\DataModel\StringResourceNode;
use PPP\Wikidata\WikibaseResourceNode;
use ValueFormatters\FormatterOptions;
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
		return array(
			array(
				new WikibaseResourceNode('', new StringValue('foo')),
				new StringResourceNode('foo'),
				null,
				new DispatchingWikibaseResourceNodeFormatter(array(
					'string' => new StringFormatter(new FormatterOptions())
				))
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
