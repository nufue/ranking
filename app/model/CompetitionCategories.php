<?php

namespace App\Model;

final class CompetitionCategories extends Base {

	public function getByYearForSelect(int $year): array {
		return $this->database->query("SELECT `id`, `select_description` FROM `competition_categories` WHERE (`year_from` IS NULL OR `year_from` <= ?) AND (`year_to` IS NULL OR `year_to` >= ?) ORDER BY `order`", $year, $year)->fetchPairs('id', 'select_description');
	}

}