<?php

namespace PPP\Wikidata\ValueFormatters;

use Doctrine\Common\Cache\ArrayCache;
use Mediawiki\Api\MediawikiApi;
use PPP\DataModel\JsonLdResourceNode;
use PPP\Wikidata\Cache\PerSiteLinkCache;
use PPP\Wikidata\Cache\WikibaseEntityCache;
use PPP\Wikidata\WikibaseEntityProvider;
use PPP\Wikidata\Wikipedia\MediawikiArticleHeader;
use PPP\Wikidata\Wikipedia\MediawikiArticleHeaderProvider;
use PPP\Wikidata\Wikipedia\MediawikiArticleImage;
use PPP\Wikidata\Wikipedia\MediawikiArticleImageProvider;
use stdClass;
use ValueFormatters\FormatterOptions;
use ValueFormatters\Test\ValueFormatterTestBase;
use ValueFormatters\ValueFormatter;
use Wikibase\Api\WikibaseFactory;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\SiteLink;

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
						),
						'image' => (object) array(
							'@type' => 'ImageObject',
							'@id' => 'http://commons.wikimedia.org/wiki/Image:Douglas_adams_portrait_cropped.jpg',
							'contentUrl' => '//upload.wikimedia.org/wikipedia/commons/c/c0/Douglas_adams_portrait_cropped.jpg',
							'name' => 'Douglas adams portrait cropped.jpg',
							'width' => 100,
							'height' => 200
						),
						'@reverse' => (object) array(
							'about'=> (object) array(
								'@type' => 'Article',
								'@id'=> 'http://en.wikipedia.org/wiki/Douglas_Adams',
								'inLanguage'=> 'en',
								'headline'=> 'Fooo barr baz gaaaaaaa...',
								'author'=> (object) array(
									'@type'=> 'Organization',
									'@id' => 'http://www.wikidata.org/entity/Q52',
									'name' => 'Wikipedia'
								),
								'license'=> 'http://creativecommons.org/licenses/by-sa/3.0/'
							)
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
						'name' => (object) array('@value' => 'Дуглас Адамс', '@language' => 'ru'),
						'@reverse' => new stdClass()
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
						'@id' => 'http://www.wikidata.org/entity/Q42',
						'@reverse' => new stdClass()
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
						'name' => (object) array('@value' => 'VIAF identifier', '@language' => 'en'),
						'@reverse' => new stdClass()
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

		$entityCache = new WikibaseEntityCache(new ArrayCache());
		$entityCache->save($this->getQ42());
		$entityCache->save($this->getP214());

		$articleHeaderCache = new PerSiteLinkCache(new ArrayCache(), 'wparticlehead');
		$articleHeaderCache->save(new MediawikiArticleHeader(
			new SiteLink('enwiki', 'Douglas Adams'),
			'Fooo barr baz gaaaaaaa...',
			'en',
			'http://en.wikipedia.org/wiki/Douglas_Adams'
		));

		$imageCache = new PerSiteLinkCache(new ArrayCache(), 'wpimg');
		$imageCache->save(new MediawikiArticleImage(
			new SiteLink('enwiki', 'Douglas Adams'),
			'//upload.wikimedia.org/wikipedia/commons/c/c0/Douglas_adams_portrait_cropped.jpg',
			100,
			200,
			'Douglas adams portrait cropped.jpg'
		));

		return new $class(
			new WikibaseEntityProvider(
				$wikibaseFactory->newRevisionsGetter(),
				$entityCache
			),
			new MediawikiArticleHeaderProvider(
				array(
					'enwiki' => new MediawikiApi('http://example.org')
				),
				$articleHeaderCache
			),
			new MediawikiArticleImageProvider(
				array(
					'enwiki' => new MediawikiApi('http://example.org')
				),
				$imageCache
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
		$item->getSiteLinkList()->addNewSiteLink('enwiki', 'Douglas Adams');

		return $item;
	}

	private function getP214() {
		$property = Property::newFromType('string');
		$property->setId(new PropertyId('P214'));
		$property->getFingerprint()->setLabel('en', 'VIAF identifier');

		return $property;
	}
}
