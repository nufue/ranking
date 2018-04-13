<?php

namespace App\Model;

final class CompetitionTypes extends Base {

	public function getByYear(int $year): array {
		return $this->database->query("SELECT `id`, `description` FROM `competition_types` WHERE (`year_from` IS NULL OR `year_from` <= ?) AND (`year_to` IS NULL OR `year_to` >= ?)", $year, $year)->fetchPairs('id', 'description');
	}

}