<?php

namespace PPP\Wikidata\ValueFormatters;

use Doctrine\Common\Cache\ArrayCache;
use Mediawiki\Api\MediawikiApi;
use PPP\Wikidata\Cache\PerSiteLinkCache;
use PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdEntityFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdItemFormatter;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdFormatterTestBase;
use PPP\Wikidata\Wikipedia\MediawikiArticle;
use PPP\Wikidata\Wikipedia\MediawikiArticleImage;
use PPP\Wikidata\Wikipedia\MediawikiArticleProvider;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\SiteLink;
use Wikibase\DataModel\SiteLinkList;

/**
 * @covers PPP\Wikidata\ValueFormatters\ExtendedJsonLdItemFormatter
 *
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class ExtendedJsonLdItemFormatterTest extends JsonLdFormatterTestBase {

	/**
	 * @see JsonLdFormatterTestBase::validProvider
	 */
	public function validProvider() {
		$item = new Item(
			new ItemId('Q42'),
			null,
			new SiteLinkList(array(new SiteLink('enwiki', 'Douglas Adams')))
		);

		return array(
			array(
				$item,
				(object) array(
					'@type' => 'Thing',
					'@id' => 'http://www.wikidata.org/entity/Q42',
					'name' => 'Q42',
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
				new FormatterOptions(array(
					JsonLdEntityFormatter::OPT_ENTITY_BASE_URI => 'http://www.wikidata.org/entity/',
					ValueFormatter::OPT_LANG => 'en'
				))
			),
			array(
				$item,
				(object) array(
					'@type' => 'Thing',
					'@id' => 'http://www.wikidata.org/entity/Q42',
					'name' => 'Q42',
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
					)
				),
				new FormatterOptions(array(
					JsonLdEntityFormatter::OPT_ENTITY_BASE_URI => 'http://www.wikidata.org/entity/',
					ValueFormatter::OPT_LANG => 'ru'
				))
			)
		);
	}

	/**
	 * @see JsonLdFormatterTestBase::getInstance
	 */
	protected function getInstance(FormatterOptions $options = null) {
		$articleHeaderCache = new PerSiteLinkCache(new ArrayCache(), 'wphead');
		$articleHeaderCache->save(new MediawikiArticle(
			new SiteLink('enwiki', 'Douglas Adams'),
			'Fooo barr baz gaaaaaaa...',
			'en',
			'http://en.wikipedia.org/wiki/Douglas_Adams',
			new MediawikiArticleImage(
				'//upload.wikimedia.org/wikipedia/commons/c/c0/Douglas_adams_portrait_cropped.jpg',
				100,
				200,
				'Douglas adams portrait cropped.jpg'
			)
		));

		return new ExtendedJsonLdItemFormatter(
			new JsonLdItemFormatter(new JsonLdEntityFormatter($options), $options),
			new MediawikiArticleProvider(
				array(
					'enwiki' => new MediawikiApi('http://example.org')
				),
				$articleHeaderCache
			),
			$options
		);
	}
}
