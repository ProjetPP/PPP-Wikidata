<?php

namespace PPP\Wikidata\ValueParsers;

use Doctrine\Common\Cache\ArrayCache;
use Mediawiki\Api\MediawikiApi;
use PPP\Wikidata\Cache\WikibaseEntityIdParserCache;
use ValueParsers\Test\ValueParserTestBase;
use ValueParsers\ValueParser;
use Wikibase\DataModel\Entity\BasicEntityIdParser;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\PropertyId;

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
				'VIAF',
				array(new EntityIdValue(new PropertyId('P214')))
			),
			array(
				'nom de naissance',
				array(
					new EntityIdValue(new PropertyId('P1477')),
					new EntityIdValue(new PropertyId('P513'))
				)
			),
			array(
				'identifiant VIAF',
				array(
					new EntityIdValue(new PropertyId('P214'))
				)
			),
			array(
				'lieu de naissan',
				array(
					new EntityIdValue(new PropertyId('P19'))
				)
			),
			array(
				'PÈRE',
				array(
					new EntityIdValue(new PropertyId('P22'))
				)
			)
		);
	}

	/**
	 * @see ValueParserTestBase::invalidInputProvider
	 */
	public function invalidInputProvider() {
		return parent::invalidInputProvider() + array(
			array(
				'abcabc'
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
		$class = $this->getParserClass();
		return new $class(
			new MediawikiApi('http://www.wikidata.org/w/api.php'),
			new BasicEntityIdParser(),
			new WikibaseEntityIdParserCache(new ArrayCache()),
			$this->newParserOptions()
		);
	}

	/**
	 * @see ValueParserTestBase::newParserOptions
	 */
	protected function newParserOptions() {
		$parserOptions = parent::newParserOptions();

		$parserOptions->setOption(ValueParser::OPT_LANG, 'fr');
		$parserOptions->setOption(WikibaseEntityIdParser::OPT_ENTITY_TYPE, 'property');

		return $parserOptions;
	}

	/**
	 * @return ValueParserTestBase::requireDataValue
	 */
	protected function requireDataValue() {
		return false;
	}
}
