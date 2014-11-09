<?php

namespace PPP\Wikidata\ValueFormatters;

use Doctrine\Common\Cache\ArrayCache;
use Mediawiki\Api\MediawikiApi;
use PPP\DataModel\StringResourceNode;
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
 * @covers PPP\Wikidata\ValueFormatters\WikibaseEntityIdFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 *
 * @todo mock instead of requests to the real API?
 */
class WikibaseEntityIdFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new EntityIdValue(new ItemId('Q42')),
				new StringResourceNode('Douglas Adams')
			),
			array(
				new EntityIdValue(new ItemId('Q42')),
				new StringResourceNode('Дуглас Адамс'),
				new FormatterOptions(array(ValueFormatter::OPT_LANG => 'ru'))
			),
			array(
				new EntityIdValue(new PropertyId('P214')),
				new StringResourceNode('VIAF identifier')
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
