<?php

namespace App\Model;

final class ScoringTables extends Base {

	/** @var array */
	private $cache = [];

	public function getByCompetitionType(string $competitionType): array {
		if (isset($this->cache[$competitionType]))
			return $this->cache[$competitionType];
		$table = [];

		$result = $this->database->query("SELECT `r`.`rank`, `r`.`points` FROM `scoring_tables_rows` `r` JOIN `competition_types_scoring` `cts` ON `r`.`id` = `cts`.`scoring_table` WHERE `cts`.`id_competition_type` = ? ORDER BY `r`.`rank`", $competitionType)->fetchAll();
		foreach ($result as $row) {
			$table[$row->rank] = $row->points;
		}
		$this->cache[$competitionType] = $table;
		return $table;
	}

}