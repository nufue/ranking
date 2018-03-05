<?php
declare(strict_types=1);

namespace App\Model;

use App\Exceptions\LeagueNotFoundException;

final class Leagues extends Base
{

	public function getLeaguesForYear(int $year): array
	{
		return $this->database->query("SELECT `id`, `name` FROM `leagues` WHERE (`year_from` IS NULL OR `year_from` <= ?) AND (`year_to` IS NULL OR `year_to` >= ?) ORDER BY `order`", $year, $year)->fetchPairs('id', 'name');
	}

	public function getName(string $league): string
	{
		$name = $this->database->query("SELECT `name` FROM `leagues` WHERE `id` = ?", $league)->fetchField();
		if ($name !== false) {
			return (string)$name;
		}
		throw new LeagueNotFoundException('Liga s označením "'.$league.'" nebyla nalezena.');
	}

}