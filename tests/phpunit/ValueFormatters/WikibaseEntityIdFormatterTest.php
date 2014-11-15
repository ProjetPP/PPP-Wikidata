<?php

namespace PPP\Wikidata\ValueFormatters;

use Doctrine\Common\Cache\ArrayCache;
use Mediawiki\Api\MediawikiApi;
use PPP\Wikidata\Cache\WikibaseEntityCache;
use PPP\Wikidata\DataModel\WikibaseEntityResourceNode;
use PPP\Wikidata\WikibaseEntityProvider;
use ValueFormatters\FormatterOptions;
use ValueFormatters\Test\ValueFormatterTestBase;
use ValueFormatters\ValueFormatter;
use Wikibase\Api\WikibaseFactory;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;

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
				new WikibaseEntityResourceNode('Douglas Adams', new ItemId('Q42'))
			),
			array(
				new EntityIdValue(new ItemId('Q42')),
				new WikibaseEntityResourceNode('Дуглас Адамс', new ItemId('Q42')),
				new FormatterOptions(array(ValueFormatter::OPT_LANG => 'ru'))
			),
			array(
				new EntityIdValue(new PropertyId('P214')),
				new WikibaseEntityResourceNode('VIAF identifier', new PropertyId('P214'))
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
		$wikibaseFactory = new WikibaseFactory(new MediawikiApi(''));

		$cache = new WikibaseEntityCache(new ArrayCache());
		$cache->save($this->getQ42());
		$cache->save($this->getP214());

		return new $class(
			new WikibaseEntityProvider(
				$wikibaseFactory->newRevisionGetter(),
				$cache
			),
			$options
		);
	}

	private function getQ42() {
		$item = Item::newEmpty();
		$item->setId( new ItemId('Q42'));
		$item->getFingerprint()->setLabel('en', 'Douglas Adams');
		$item->getFingerprint()->setLabel('ru', 'Дуглас Адамс');

		return $item;
	}

	private function getP214() {
		$property = Property::newFromType('string');
		$property->setId(new PropertyId('P214'));
		$property->getFingerprint()->setLabel('en', 'VIAF identifier');

		return $property;
	}
}
