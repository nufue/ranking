<?php

namespace App\Model;

final class TeamMembersCount extends Base
{

	public function getByYear(int $year): int
	{
		$result = $this->database->query("SELECT `count` FROM `team_members_count` WHERE (`year_from` IS NULL OR `year_from` <= ?) AND (`year_to` IS NULL OR `year_to` >= ?)", $year, $year)->fetchField();
		if ($result !== false) {
			return (int)$result;
		}
		return 15; // default value
	}

	public function getAll(): array
	{
		return $this->database->query("SELECT `id`, `count`, `year_from`, `year_to` FROM `team_members_count` ORDER BY `year_from`, `year_to`")->fetchPairs('id');
	}

	public function overlaps(?int $from, ?int $to): bool
	{
		foreach ($this->getAll() as $mc) {
			if ($this->yearsOverlap($mc->year_from, $mc->year_to, $from, $to)) {
				return true;
			}
		}
		return false;
	}

	private function yearsOverlap(?int $from1, ?int $to1, ?int $from2, ?int $to2): bool {
		if ($from1 === null && $from2 === null)
			return true;
		if ($to1 === null && $to2 === null)
			return true;
		if ($to1 === null && $from2 > $from1)
			return false;
		if ($from1 !== null && $to1 === null && $to2 !== null)
			return $from1 < $to2;
		if ($to1 !== null && $from1 === null && $from2 !== null)
			return $to1 > $from2;
		if ($from1 !== null && $to1 !== null && $from2 === null && $to2 !== null)
			return $to2 > $from1;
		if ($from1 !== null && $to1 !== null && $from2 !== null && $to2 === null)
			return $from2 < $to1;
		if ($from1 !== null && $to1 !== null && $from2 !== null && $to2 !== null) {
			return ($to2 >= $from1 && $to2 <= $to1) || ($from2 >= $from1 && $from2 <= $to1);
		}
		return false;
	}

}
