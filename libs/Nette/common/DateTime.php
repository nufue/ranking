<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette;

use Nette;


/**
 * DateTime with serialization and timestamp support for PHP 5.2.
 *
 * @author     David Grudl
 */
class DateTime extends \DateTime
{
	/** minute in seconds */
	const MINUTE = 60;

	/** hour in seconds */
	const HOUR = 3600;

	/** day in seconds */
	const DAY = 86400;

	/** week in seconds */
	const WEEK = 604800;

	/** average month in seconds */
	const MONTH = 2629800;

	/** average year in seconds */
	const YEAR = 31557600;


	/**
	 * DateTime object factory.
	 * @param  string|int|\DateTime
	 * @return DateTime
	 */
	public static function from($time)
	{
		if ($time instanceof \DateTime) {
			return new static($time->format('Y-m-d H:i:s'), $time->getTimezone());

		} elseif (is_numeric($time)) {
			if ($time <= self::YEAR) {
				$time += time();
			}
			$tmp = new static('@' . $time);
			$tmp->setTimeZone(new \DateTimeZone(date_default_timezone_get()));
			return $tmp;

		} else { // textual or NULL
			return new static($time);
		}
	}


	public function __toString()
	{
		return $this->format('Y-m-d H:i:s');
	}


	public function modifyClone($modify = '')
	{
		$dolly = clone $this;
		return $modify ? $dolly->modify($modify) : $dolly;
	}


	public function setTimestamp($timestamp)
	{
		$zone = PHP_VERSION_ID === 50206 ? new \DateTimeZone($this->getTimezone()->getName()) : $this->getTimezone();
		$this->__construct('@' . $timestamp);
		$this->setTimeZone($zone);
		return $this;
	}


	public function getTimestamp()
	{
		$ts = $this->format('U');
		return is_float($tmp = $ts * 1) ? $ts : $tmp;
	}


	}
