<?php

namespace App\Model;

use Nette\Database\Connection;

final class CountedCompetitions extends Base
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
		$result = $this->database->query("SELECT `count` FROM `counted_competitions` WHERE (`year_from` IS NULL OR `year_from` <= ?) AND (`year_to` IS NULL OR `year_to` >= ?)", $year, $year)->fetchField();
		if ($result !== false) {
			return (int)$result;
		}
		return 12; // default value
	}

	public function getAll(): array
	{
		return $this->database->query("SELECT `id`, `count`, `year_from`, `year_to` FROM `counted_competitions` ORDER BY `year_from`, `year_to`")->fetchPairs('id');
	}

	public function add(?int $from, ?int $to, int $count): void
	{
		if ($from !== null) {
			$this->database->query("UPDATE `counted_competitions` SET `year_to` = ? WHERE `year_to` IS NULL", $from - 1);
		}
		$this->database->query("INSERT INTO `counted_competitions`(`count`, `year_from`, `year_to`) VALUES (?, ?, ?)", $count, $from, $to);
	}

	public function update(int $id, ?int $to, int $count): void
	{
		$this->database->query("UPDATE `counted_competitions` SET `count` = ?, `year_to` = ? WHERE `id` = ?", $count, $to, $id);
	}

	public function overlaps(?int $from, ?int $to, ?int $editId): bool
	{
		foreach ($this->getAll() as $id => $cc) {
			if ($editId === $id) {
				continue;
			}
			if ($cc->year_to === null && $from > $cc->year_from) {
				continue;
			}
			if ($this->yearsOverlap->isOverlapped($cc->year_from, $cc->year_to, $from, $to)) {
				return true;
			}
		}
		return false;
	}

}
