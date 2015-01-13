<?php

namespace PPP\Wikidata\ValueFormatters;

use Doctrine\Common\Cache\ArrayCache;
use Mediawiki\Api\MediawikiApi;
use PPP\DataModel\JsonLdResourceNode;
use PPP\Wikidata\Cache\WikibaseEntityCache;
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
				new JsonLdResourceNode(
					'Douglas Adams',
					(object) array(
						'@context' => 'http://schema.org',
						'@type' => 'Thing',
						'@id' => 'http://www.wikidata.org/entity/Q42',
						'name' => (object) array('@value' => 'Douglas Adams', '@language' => 'en'),
						'description' => (object) array('@value' => 'Author', '@language' => 'en'),
						'alternateName' => array(
							(object) array('@value' => '42', '@language' => 'en')
						)
					)
				),
				new FormatterOptions(array(ValueFormatter::OPT_LANG => 'en'))
			),
			array(
				new EntityIdValue(new ItemId('Q42')),
				new JsonLdResourceNode(
					'Дуглас Адамс',
					(object) array(
						'@context' => 'http://schema.org',
						'@type' => 'Thing',
						'@id' => 'http://www.wikidata.org/entity/Q42',
						'name' => (object) array('@value' => 'Дуглас Адамс', '@language' => 'ru')
					)
				),
				new FormatterOptions(array(ValueFormatter::OPT_LANG => 'ru'))
			),
			array(
				new EntityIdValue(new ItemId('Q42')),
				new JsonLdResourceNode(
					'',
					(object) array(
						'@context' => 'http://schema.org',
						'@type' => 'Thing',
						'@id' => 'http://www.wikidata.org/entity/Q42'
					)
				),
				new FormatterOptions(array(ValueFormatter::OPT_LANG => 'de'))
			),
			array(
				new EntityIdValue(new PropertyId('P214')),
				new JsonLdResourceNode(
					'VIAF identifier',
					(object) array(
						'@context' => 'http://schema.org',
						'@type' => 'Thing',
						'@id' => 'http://www.wikidata.org/entity/P214',
						'name' => (object) array('@value' => 'VIAF identifier', '@language' => 'en')
					)
				)
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
				$wikibaseFactory->newRevisionsGetter(),
				$cache
			),
			$options
		);
	}

	private function getQ42() {
		$item = Item::newEmpty();
		$item->setId( new ItemId('Q42'));
		$item->getFingerprint()->setLabel('en', 'Douglas Adams');
		$item->getFingerprint()->setDescription('en', 'Author');
		$item->getFingerprint()->setAliasGroup('en', array('42'));
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
