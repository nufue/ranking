<?php

namespace App\Model;

final class TeamNameOverrides extends Base
{

	public function getByYear(int $year): array
	{
		$result = [];
		$overrides = $this->database->query("SELECT `competitor`, `team_name` FROM `team_name_override` WHERE `year` = ?", $year)->fetchAll();
		foreach ($overrides as $row) {
			$result[$row->competitor] = $row->team_name;
		}
		return $result;
	}

	public function getAll(): array
	{
		$result = [];
		$overrides = $this->database->query("SELECT `z`.`id`, `z`.`registrace`, `z`.`cele_jmeno`, `tno`.`team_name`, `tno`.`year` FROM `team_name_override` `tno` JOIN `zavodnici` `z` ON `tno`.`competitor` = `z`.`id` ORDER BY `tno`.`year`")->fetchAll();
		foreach ($overrides as $row) {
			$result[] = ['id' => $row->id, 'registration' => $row->registrace, 'fullName' => $row->cele_jmeno, 'team' => $row->team_name, 'year' => $row->year];
		}
		return $result;
	}

	public function add(int $year, int $competitor, string $teamName): void
	{
		$this->database->query("INSERT INTO `team_name_override`(`year`, `competitor`, `team_name`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `team_name` = ?", $year, $competitor, $teamName, $teamName);
	}

	public function remove(int $id, int $year): void {
		$this->database->query("DELETE FROM `team_name_override` WHERE `competitor` = ? AND `year` = ? LIMIT 1", $id, $year);
	}

}