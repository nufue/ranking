<?php

namespace App\Model;

final class TeamNameOverrides extends Base {

	public function getByYear(int $year): array {
		$result = [];
		$overrides = $this->database->query("SELECT `competitor`, `team_name` FROM `team_name_override` WHERE `year` = ?", $year)->fetchAll();
		foreach ($overrides as $row) {
			$result[$row->competitor] = $row->team_name;
		}
		return $result;
	}

}