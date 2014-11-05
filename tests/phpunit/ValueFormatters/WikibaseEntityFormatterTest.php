<?php

namespace PPP\Wikidata\ValueFormatters;

use Doctrine\Common\Cache\ArrayCache;
use Mediawiki\Api\MediawikiApi;
use PPP\Wikidata\Cache\WikibaseEntityCache;
use PPP\Wikidata\WikibaseEntityProvider;
use ValueFormatters\FormatterOptions;
use ValueFormatters\Test\ValueFormatterTestBase;
use ValueFormatters\ValueFormatter;
use Wikibase\Api\WikibaseFactory;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers PPP\Wikidata\ValueFormatters\WikibaseEntityFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseEntityFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new EntityIdValue(new ItemId('Q42')),
				'Douglas Adams'
			),
			array(
				new EntityIdValue(new ItemId('Q42')),
				'Дуглас Адамс',
				new FormatterOptions(array(ValueFormatter::OPT_LANG => 'ru'))
			),
			array(
				new EntityIdValue(new PropertyId('P214')),
				'VIAF identifier'
			)
		);
	}

	/**
	 * @see ValueFormatterTestBase::getFormatterClass
	 */
	protected function getFormatterClass() {
		return 'PPP\Wikidata\ValueFormatters\WikibaseEntityFormatter';
	}


	/**
	 * @see ValueFormatterTestBase::getInstance
	 */
	protected function getInstance(FormatterOptions $options) {
		$class = $this->getFormatterClass();
		$wikibaseFactory = new WikibaseFactory(new MediawikiApi('http://www.wikidata.org/w/api.php'));
		return new $class(
			new WikibaseEntityProvider(
				$wikibaseFactory->newRevisionGetter(),
				new WikibaseEntityCache(new ArrayCache())
			),
			$options
		);
	}
}
