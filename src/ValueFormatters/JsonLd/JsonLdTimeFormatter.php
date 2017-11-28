<?php

namespace PPP\Wikidata\ValueFormatters\JsonLd;

use DataValues\TimeValue;
use InvalidArgumentException;
use PPP\Wikidata\ValueFormatters\IsoTimeFormatter;
use stdClass;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatterBase;

/**
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 * @todo support calendars
 */
class JsonLdTimeFormatter extends ValueFormatterBase implements JsonLdDataValueFormatter {

	/**
	 * @var IsoTimeFormatter
	 */
	private $timeToIsoFormatter;

	/**
	 * @param IsoTimeFormatter $timeToIsoFormatter
	 * @param FormatterOptions|null $options
	 */
	public function __construct(
		IsoTimeFormatter $timeToIsoFormatter,
		FormatterOptions $options = null
	) {
		$this->timeToIsoFormatter = $timeToIsoFormatter;

		parent::__construct($options);
	}

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof TimeValue)) {
			throw new InvalidArgumentException('$value is not a TimeValue.');
		}

		return $this->toJsonLd($value);
	}

	private function toJsonLd(TimeValue $value) {
		$literal = new stdClass();
		$literal->{'@value'} = $this->timeToIsoFormatter->format($value);

		if($value->getPrecision() < TimeValue::PRECISION_HOUR) {
			$literal->{'@type'} = 'Date';
		} else {
			$literal->{'@type'} = 'DateTime';
		}

		return $literal;
	}
}
