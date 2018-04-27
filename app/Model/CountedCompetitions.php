<?php

namespace App\Model;

final class CountedCompetitions extends Base
{

	public function getByYear(int $year): int
	{
		$result = $this->database->query("SELECT `count` FROM `counted_competitions` WHERE (`year_from` IS NULL OR `year_from` <= ?) AND (`year_to` IS NULL OR `year_to` >= ?)", $year, $year)->fetchField();
		if ($result !== false) {
			return (int)$result;
		}
		return 12; // default value
	}

}
