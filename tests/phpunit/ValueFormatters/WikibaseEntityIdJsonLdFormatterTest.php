<?php

namespace PPP\Wikidata\ValueFormatters;

use Doctrine\Common\Cache\ArrayCache;
use Mediawiki\Api\MediawikiApi;
use PPP\Wikidata\Cache\PerSiteLinkCache;
use PPP\Wikidata\Wikipedia\MediawikiArticleHeader;
use PPP\Wikidata\Wikipedia\MediawikiArticleHeaderProvider;
use PPP\Wikidata\Wikipedia\MediawikiArticleImage;
use PPP\Wikidata\Wikipedia\MediawikiArticleImageProvider;
use stdClass;
use ValueFormatters\FormatterOptions;
use ValueFormatters\Test\ValueFormatterTestBase;
use ValueFormatters\ValueFormatter;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\SiteLink;
use Wikibase\EntityStore\InMemory\InMemoryEntityStore;

/**
 * @covers PPP\Wikidata\ValueFormatters\WikibaseEntityIdJsonLdFormatter
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseEntityIdJsonLdFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				new ItemId('Q42'),
				(object) array(
					'@type' => 'Thing',
					'@id' => 'http://www.wikidata.org/entity/Q42',
					'name' => (object) array('@value' => 'Douglas Adams', '@language' => 'en'),
					'description' => (object) array('@value' => 'Author', '@language' => 'en'),
					'alternateName' => array(
						(object) array('@value' => '42', '@language' => 'en')
					),
					'potentialAction' => array(
						(object) array(
							'@type' => 'ViewAction',
							'name' => array(
								(object) array('@value' => 'View on Wikidata', '@language' => 'en'),
								(object) array('@value' => 'Voir sur Wikidata', '@language' => 'fr')
							),
							'image' => '//upload.wikimedia.org/wikipedia/commons/f/ff/Wikidata-logo.svg',
							'target' => '//www.wikidata.org/entity/Q42'
						),
						(object) array(
							'@type' => 'ViewAction',
							'name' => array(
								(object) array('@value' => 'View on Wikipedia', '@language' => 'en'),
								(object) array('@value' => 'Voir sur Wikipédia', '@language' => 'fr')
							),
							'image' => '//upload.wikimedia.org/wikipedia/commons/thumb/8/80/Wikipedia-logo-v2.svg/64px-Wikipedia-logo-v2.svg.png',
							'target' => 'http://en.wikipedia.org/wiki/Douglas_Adams'
						)
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
				),
				new FormatterOptions(array(ValueFormatter::OPT_LANG => 'en'))
			),
			array(
				new ItemId('Q42'),
				(object) array(
					'@type' => 'Thing',
					'@id' => 'http://www.wikidata.org/entity/Q42',
					'name' => (object) array('@value' => 'Дуглас Адамс', '@language' => 'ru'),
					'potentialAction' => array(
						(object) array(
							'@type' => 'ViewAction',
							'name' => array(
								(object) array('@value' => 'View on Wikidata', '@language' => 'en'),
								(object) array('@value' => 'Voir sur Wikidata', '@language' => 'fr')
							),
							'image' => '//upload.wikimedia.org/wikipedia/commons/f/ff/Wikidata-logo.svg',
							'target' => '//www.wikidata.org/entity/Q42'
						)
					),
					'@reverse' => new stdClass()
				),
				new FormatterOptions(array(ValueFormatter::OPT_LANG => 'ru'))
			),
			array(
				new ItemId('Q42'),
				(object) array(
					'@type' => 'Thing',
					'@id' => 'http://www.wikidata.org/entity/Q42',
					'potentialAction' => array(
						(object) array(
							'@type' => 'ViewAction',
							'name' => array(
								(object) array('@value' => 'View on Wikidata', '@language' => 'en'),
								(object) array('@value' => 'Voir sur Wikidata', '@language' => 'fr')
							),
							'image' => '//upload.wikimedia.org/wikipedia/commons/f/ff/Wikidata-logo.svg',
							'target' => '//www.wikidata.org/entity/Q42'
						)
					),
					'@reverse' => new stdClass()
				),
				new FormatterOptions(array(ValueFormatter::OPT_LANG => 'de'))
			),
			array(
				new PropertyId('P214'),
				(object) array(
					'@type' => 'Thing',
					'@id' => 'http://www.wikidata.org/entity/P214',
					'name' => (object) array('@value' => 'VIAF identifier', '@language' => 'en'),
					'potentialAction' => array(
						(object) array(
							'@type' => 'ViewAction',
							'name' => array(
								(object) array('@value' => 'View on Wikidata', '@language' => 'en'),
								(object) array('@value' => 'Voir sur Wikidata', '@language' => 'fr')
							),
							'image' => '//upload.wikimedia.org/wikipedia/commons/f/ff/Wikidata-logo.svg',
							'target' => '//www.wikidata.org/entity/P214'
						)
					),
					'@reverse' => new stdClass()
				)
			)
		);
	}

	/**
	 * @see ValueFormatterTestBase::getFormatterClass
	 */
	protected function getFormatterClass() {
		return 'PPP\Wikidata\ValueFormatters\WikibaseEntityIdJsonLdFormatter';
	}


	/**
	 * @see ValueFormatterTestBase::getInstance
	 */
	protected function getInstance(FormatterOptions $options) {
		$class = $this->getFormatterClass();

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
			new InMemoryEntityStore(array($this->getQ42(), $this->getP214())),
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
