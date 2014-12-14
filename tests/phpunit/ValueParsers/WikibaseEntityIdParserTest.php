<?php

namespace PPP\Wikidata\ValueParsers;

use Doctrine\Common\Cache\ArrayCache;
use Mediawiki\Api\MediawikiApi;
use PPP\Wikidata\Cache\WikibaseEntityCache;
use PPP\Wikidata\Cache\WikibaseEntityIdParserCache;
use PPP\Wikidata\WikibaseEntityProvider;
use ValueParsers\Test\ValueParserTestBase;
use ValueParsers\ValueParser;
use Wikibase\Api\WikibaseFactory;
use Wikibase\DataModel\Entity\BasicEntityIdParser;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers PPP\Wikidata\ValueParsers\WikibaseEntityIdParser
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 *
 * @todo mock instead of requests to the real API?
 */
class WikibaseEntityIdParserTest extends ValueParserTestBase {

	/**
	 * @see ValueParserTestBase::validInputProvider
	 */
	public function validInputProvider() {
		return array(
			array(
				'Douglas Adams',
				array(new EntityIdValue(new ItemId('Q42')))
			),
			array(
				'Barack Obama',
				array(new EntityIdValue(new ItemId('Q76')))
			),
			array(
				'P=NP',
				array(new EntityIdValue(new ItemId('Q746242')))
			),
			array(
				'TUNGSTÈNE',
				array(
					new EntityIdValue(new ItemId('Q743')),
					new EntityIdValue(new ItemId('Q3542087'))
				)
			),
			array(
				'Douglas Noel Ada',
				array(new EntityIdValue(new ItemId('Q42')))
			),
			array(
				'',
				array()
			),
			array(
				'abcabcabc',
				array()
			),
		);
	}

	/**
	 * @see ValueParserTestBase::invalidInputProvider
	 */
	public function invalidInputProvider() {
		return parent::invalidInputProvider() + array(
			array(
				new ItemId('Q23')
			)
		);
	}

	/**
	 * @see ValueParserTestBase::getParserClass
	 */
	protected function getParserClass() {
		return 'PPP\Wikidata\ValueParsers\WikibaseEntityIdParser';
	}

	/**
	 * @see ValueParserTestBase::getInstance
	 */
	protected function getInstance() {
		$api = new MediawikiApi('http://www.wikidata.org/w/api.php');
		$wikibaseFactory = new WikibaseFactory($api);

		$class = $this->getParserClass();
		return new $class(
			$api,
			new BasicEntityIdParser(),
			new WikibaseEntityIdParserCache(new ArrayCache()),
			new WikibaseEntityProvider($wikibaseFactory->newRevisionsGetter(), new WikibaseEntityCache(new ArrayCache())),
			$this->newParserOptions()
		);
	}

	/**
	 * @see ValueParserTestBase::newParserOptions
	 */
	protected function newParserOptions() {
		$parserOptions = parent::newParserOptions();

		$parserOptions->setOption(ValueParser::OPT_LANG, 'fr');
		$parserOptions->setOption(WikibaseEntityIdParser::OPT_ENTITY_TYPE, 'item');

		return $parserOptions;
	}

	/**
	 * @return ValueParserTestBase::requireDataValue
	 */
	protected function requireDataValue() {
		return false;
	}
}
