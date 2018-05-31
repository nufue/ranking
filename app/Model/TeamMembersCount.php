<?php

namespace App\Model;

use Nette\Database\Connection;

final class TeamMembersCount extends Base
{

	/** @var YearsOverlap */
	private $yearsOverlap;

	public function __construct(Connection $db, YearsOverlap $yearsOverlap)
	{
		parent::__construct($db);
		$this->yearsOverlap = $yearsOverlap;
	}

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

	public function add(?int $from, ?int $to, int $count): void
	{
		if ($from !== null) {
			$this->database->query("UPDATE `team_members_count` SET `year_to` = ? WHERE `year_to` IS NULL", $from - 1);
		}
		$this->database->query("INSERT INTO `team_members_count`(`count`, `year_from`, `year_to`) VALUES (?, ?, ?)", $count, $from, $to);
	}

	public function update(int $id, ?int $to, int $count): void
	{
		$this->database->query("UPDATE `team_members_count` SET `count` = ?, `year_to` = ? WHERE `id` = ?", $count, $to, $id);
	}

	public function overlaps(?int $from, ?int $to, ?int $editId): bool
	{
		foreach ($this->getAll() as $id => $mc) {
			if ($editId === $id) {
				continue;
			}
			if ($mc->year_to === null && $from > $mc->year_from) {
				continue;
			}
			if ($this->yearsOverlap->isOverlapped($mc->year_from, $mc->year_to, $from, $to)) {
				return true;
			}
		}
		return false;
	}

}
