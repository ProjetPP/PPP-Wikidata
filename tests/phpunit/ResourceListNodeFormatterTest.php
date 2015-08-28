<?php

namespace PPP\Wikidata;

use DataValues\StringValue;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\StringResourceNode;
use PPP\Module\TreeSimplifier\NodeSimplifierBaseTest;
use PPP\Wikidata\ValueFormatters\DispatchingWikibaseResourceNodeFormatter;
use PPP\Wikidata\ValueFormatters\StringFormatter;

/**
 * @covers PPP\Wikidata\ResourceListNodeFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class ResourceListNodeFormatterTest extends NodeSimplifierBaseTest {

	protected function buildSimplifier() {
		$formatterMock = $this->getMock('ValueFormatters\ValueFormatter');
		$formatterMock->expects($this->any())
			->method('format')
			->with($this->equalTo(new WikibaseResourceNode('', new StringValue('foo'))))
			->will($this->returnValue(new StringResourceNode('foo')));

		return new ResourceListNodeFormatter(
			$formatterMock
		);
	}

	public function simplifiableProvider() {
		return array(
			array(
				new ResourceListNode()
			)
		);
	}

	public function nonSimplifiableProvider() {
		return array(
			array(
				new MissingNode()
			)
		);
	}

	public function simplificationProvider() {
		return array(
			array(
				new ResourceListNode(),
				new ResourceListNode()
			),
			array(
				new ResourceListNode(array(
					new StringResourceNode('foo')
				)),
				new ResourceListNode(array(
					new StringResourceNode('foo')
				))
			),
			array(
				new ResourceListNode(array(
					new StringResourceNode('foo')
				)),
				new ResourceListNode(array(
					new WikibaseResourceNode('', new StringValue('foo'))
				))
			),
		);
	}
}
