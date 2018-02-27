<?php

namespace App\Model;

final class Utils
{

	public static function convertDate($date): \DateTime
	{
		$date = str_replace(". ", ".", $date);
		$dt = strtotime($date);
		return new \DateTime(Date("Y-m-d", $dt));
	}

}