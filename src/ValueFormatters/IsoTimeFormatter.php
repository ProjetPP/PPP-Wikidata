<?php

namespace PPP\Wikidata\ValueFormatters;

use DataValues\TimeValue;
use InvalidArgumentException;
use ValueFormatters\ValueFormatterBase;

/**
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 * @todo Move to data-value/Time
 */
class IsoTimeFormatter extends ValueFormatterBase {

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof TimeValue)) {
			throw new InvalidArgumentException('DataValue is not a TimeValue.');
		}

		return $this->simplifyIsoTime($value);
	}

	private function simplifyIsoTime(TimeValue $value) {
		$parts = $this->explodeISOTime($value->getTime());
		$precision = $value->getPrecision();

		$iso = '';
		if($parts['year'] < 0) {
			$iso .= '-' . $this->toStringOfSize(-1 * $parts['year'], 4);
		} else {
			$iso .= $this->toStringOfSize($parts['year'], 4);
		}

		//Month
		if($precision < TimeValue::PRECISION_MONTH) {
			return $iso;
		}
		$iso .= '-' . $this->toStringOfSize($parts['month'], 2);

		//Day
		if($precision < TimeValue::PRECISION_DAY) {
			return $iso;
		}
		$iso .= '-' . $this->toStringOfSize($parts['day'], 2);

		//Hours
		if($precision < TimeValue::PRECISION_HOUR) {
			return $iso;
		}
		$iso .= 'T' . $this->toStringOfSize($parts['hour'], 2);

		//Minutes
		if($precision < TimeValue::PRECISION_MINUTE) {
			return $iso . $this->timezoneToIso($value->getTimezone());
		}
		$iso .= ':' . $this->toStringOfSize($parts['minute'], 2);

		//Seconds
		if($precision < TimeValue::PRECISION_SECOND) {
			return $iso . $this->timezoneToIso($value->getTimezone());
		}
		$iso .= ':' . $this->toStringOfSize($parts['second'], 2);

		return $iso . $this->timezoneToIso($value->getTimezone());
	}

	private function toStringOfSize($number, $size) {
		return str_pad((int) $number, $size, '0', STR_PAD_LEFT);
	}

	private function timezoneToIso($timezone) {
		if($timezone === 0) {
			return 'Z';
		} elseif($timezone > 0) {
			return '+' . $this->toStringOfSize($timezone / 60, 2) . ':' . $this->toStringOfSize($timezone % 60, 2);
		} else {
			return '-' . $this->toStringOfSize((-1 * $timezone) / 60, 2) . ':' . $this->toStringOfSize((-1 * $timezone) % 60, 2);
		}
	}

	private function explodeISOTime($time) {
		if(!preg_match('/([+-]\d+)-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})Z/', $time, $m)) {
			throw new InvalidArgumentException('$time is not a valid time');
		}

		return array(
			'year' => intval($m[1]),
			'month' => intval($m[2]),
			'day' => intval($m[3]),
			'hour' => intval($m[4]),
			'minute' => intval($m[5]),
			'second' => intval($m[6])
		);
	}
}
