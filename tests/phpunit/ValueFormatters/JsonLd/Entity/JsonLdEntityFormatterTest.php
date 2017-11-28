<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd\Entity;

use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdFormatterTestBase;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\SiteLink;
use Wikibase\DataModel\SiteLinkList;
use Wikibase\DataModel\Term\AliasGroup;
use Wikibase\DataModel\Term\AliasGroupList;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\Term;
use Wikibase\DataModel\Term\TermList;

/**
 * @covers PPP\Wikidata\ValueFormatters\JsonLd\Entity\JsonLdEntityFormatter
 *
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
class JsonLdEntityFormatterTest extends JsonLdFormatterTestBase {

	/**
	 * @see JsonLdFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			array(
				$this->getQ42(),
				(object) array(
					'@type' => 'Thing',
					'@id' => 'http://www.wikidata.org/entity/Q42',
					'name' => (object) array('@value' => 'Douglas Adams', '@language' => 'en'),
					'description' => (object) array('@value' => 'Author', '@language' => 'en')
				),
				new FormatterOptions(array(
					ValueFormatter::OPT_LANG => 'en',
					JsonLdEntityFormatter::OPT_ENTITY_BASE_URI => 'http://www.wikidata.org/entity/'
				))
			),
			array(
				$this->getQ42(),
				(object) array(
					'@type' => 'Thing',
					'@id' => 'http://www.wikidata.org/entity/Q42',
					'name' => (object) array('@value' => 'Дуглас Адамс', '@language' => 'ru')
				),
				new FormatterOptions(array(
					ValueFormatter::OPT_LANG => 'ru',
					JsonLdEntityFormatter::OPT_ENTITY_BASE_URI => 'http://www.wikidata.org/entity/'
				))
			),
			array(
				$this->getQ42(),
				(object) array(
					'@type' => 'Thing',
					'@id' => 'http://www.wikidata.org/entity/Q42',
					'name' => 'Q42'
				),
				new FormatterOptions(array(
					ValueFormatter::OPT_LANG => 'de',
					JsonLdEntityFormatter::OPT_ENTITY_BASE_URI => 'http://www.wikidata.org/entity/'
				))
			),
			array(
				$this->getP214(),
				(object) array(
					'@type' => 'Thing',
					'@id' => 'http://www.wikidata.org/entity/P214',
					'name' => (object) array('@value' => 'VIAF identifier', '@language' => 'en')
				),
				new FormatterOptions(array(
					JsonLdEntityFormatter::OPT_ENTITY_BASE_URI => 'http://www.wikidata.org/entity/'
				))
			)
		);
	}

	/**
	 * @see JsonLdFormatterTestBase::getInstance
	 */
	protected function getInstance(FormatterOptions $options = null) {
		return new JsonLdEntityFormatter($options);
	}

	private function getQ42() {
		return new Item(
			new ItemId('Q42'),
			new Fingerprint(
				new TermList(array(new Term('en', 'Douglas Adams'), new Term('ru', 'Дуглас Адамс'))),
				new TermList(array(new Term('en', 'Author'))),
				new AliasGroupList(array(new AliasGroup('en', array('42'))))
			),
			new SiteLinkList(array(new SiteLink('enwiki', 'Douglas Adams')))
		);
	}

	private function getP214() {
		return new Property(
			new PropertyId('P214'),
			new Fingerprint(
				new TermList(array(new Term('en', 'VIAF identifier')))
			),
			'string'
		);
	}
}
