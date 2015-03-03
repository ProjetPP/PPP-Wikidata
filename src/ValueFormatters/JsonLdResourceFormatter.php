<?php

namespace PPP\Wikidata\ValueFormatters;

use InvalidArgumentException;
use PPP\DataModel\JsonLdResourceNode;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdDataValueFormatter;
use PPP\Wikidata\WikibaseResourceNode;
use stdClass;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatterBase;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 * @todo builds @reverse from fromSubject and fromPredicate
 */
class JsonLdResourceFormatter extends ValueFormatterBase {

	/**
	 * @var JsonLdDataValueFormatter
	 */
	private $jsonLdFormatter;

	/**
	 * @param JsonLdDataValueFormatter $jsonLdFormatter
	 * @param FormatterOptions $options
	 */
	public function __construct(JsonLdDataValueFormatter $jsonLdFormatter, FormatterOptions $options) {
		$this->jsonLdFormatter = $jsonLdFormatter;

		parent::__construct($options);
	}

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof WikibaseResourceNode)) {
			throw new InvalidArgumentException('$value is not a WikibaseResourceNode.');
		}

		$resource = $this->jsonLdFormatter->format($value->getDataValue());
		$resource->{'@context'} = 'http://schema.org';

		return new JsonLdResourceNode(
			$this->getName($resource),
			$resource
		);
	}

	private function getName(stdClass $resource) {
		$namesByLanguage = array();
		$names = $resource->name;

		if(!is_array($names)) {
			$names = array($names);
		}

		foreach($names as $name) {
			if(is_object($name)) {
				$namesByLanguage[$name->{'@language'}] = $name->{'@value'};
			} else {
				return $name;
			}
		}

		$language = $this->getOption(self::OPT_LANG);
		if(array_key_exists($language, $namesByLanguage)) {
			return $namesByLanguage[$language];
		} else {
			return reset($namesByLanguage);
		}
	}
}
