<?php

namespace PPP\Wikidata\ValueFormatters;

use InvalidArgumentException;
use PPP\DataModel\JsonLdResourceNode;
use PPP\Wikidata\ValueFormatters\JsonLd\JsonLdDataValueFormatter;
use PPP\Wikidata\WikibaseResourceNode;
use stdClass;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;
use ValueFormatters\ValueFormatterBase;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Snak\PropertyValueSnak;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class JsonLdResourceFormatter extends ValueFormatterBase {

	/**
	 * @var JsonLdDataValueFormatter
	 */
	private $jsonLdFormatter;

	/**
	 * @var ValueFormatter
	 */
	private $snakFormatter;

	/**
	 * @param JsonLdDataValueFormatter $jsonLdFormatter
	 * @param ValueFormatter $snakFormatter
	 * @param FormatterOptions $options
	 */
	public function __construct(JsonLdDataValueFormatter $jsonLdFormatter, ValueFormatter $snakFormatter, FormatterOptions $options) {
		$this->jsonLdFormatter = $jsonLdFormatter;
		$this->snakFormatter = $snakFormatter;

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
		$this->addContextToResource($value, $resource);

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

	private function addContextToResource(WikibaseResourceNode $resourceNode, stdClass $resource) {
		$fromPredicate = $resourceNode->getFromPredicate();
		$fromSubject = $resourceNode->getFromSubject();
		if($fromPredicate === null || $fromSubject === null) {
			return;
		}

		$formatted = $this->snakFormatter->format(new PropertyValueSnak($fromPredicate, new EntityIdValue($fromSubject)));

		if(!property_exists($resource, '@reverse')) {
			$resource->{'@reverse'} = new stdClass();
		}

		foreach($formatted as $property => $value) {
			$resource->{'@reverse'}->{$property}[] = $value;
		}
	}
}
