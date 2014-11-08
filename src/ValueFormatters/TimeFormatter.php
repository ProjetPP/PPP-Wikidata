<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\TimeValue;
use InvalidArgumentException;
use PPP\DataModel\TimeResourceNode;
use ValueFormatters\ValueFormatterBase;
use ValueParsers\TimeParser;

/**
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 *
 * @todo cut time representation at the precision
 */
class TimeFormatter extends ValueFormatterBase {

	private static $CALENDAR_NAMES = array(
		TimeParser::CALENDAR_GREGORIAN => 'gregorian',
		TimeParser::CALENDAR_JULIAN => 'julian'
	);

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof TimeValue)) {
			throw new InvalidArgumentException('DataValue is not a TimeValue.');
		}

		return new TimeResourceNode($this->simplifyIsoTime($value->getTime()), $this->getCalendarName($value->getCalendarModel()));
	}

	private function simplifyIsoTime($time) {
		return preg_replace('/^\+0+/', '', preg_replace('/^\-0+/', '-', $time));
	}

	private function getCalendarName($calendarModel) {
		if(array_key_exists($calendarModel, self::$CALENDAR_NAMES)) {
			return self::$CALENDAR_NAMES[$calendarModel];
		} else {
			return 'gregorian';
		}
	}
}
