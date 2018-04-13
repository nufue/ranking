<?php

namespace App\Model;

use App\Exceptions\TeamNotFoundException;
use Nette\Utils\ArrayHash;

final class Teams extends Base
{

	/**
	 * @return Team[]
	 */
	public function loadTeamsByYear(int $year): array
	{
		$teams = [];
		$result = $this->database->query("SELECT `t`.*, count(`tz`.`id_zavodnika`) `pocet_zavodniku` FROM `tymy` `t` LEFT JOIN `tymy_zavodnici` `tz` ON `t`.`id` = `tz`.`id_tymu` WHERE `t`.`rok` = ? AND `t`.`nazev_tymu` != '' GROUP BY `id` ORDER BY `liga`, `kod`", $year)->fetchAll();
		foreach ($result as $row) {
			$teams[] = new Team($row->id, $row->rok, $row->liga, $row->nazev_tymu, $row->kod, $row->pocet_zavodniku);
		}
		return $teams;
	}

	/**
	 * @return Team[]
	 */
	public function loadByYearAndLeague(int $year, string $league): array {
		$teams = [];
		$result = $this->database->query("SELECT `id`, `rok`, `liga`, `nazev_tymu`, `kod`, 0 `pocet_zavodniku` FROM `tymy` `t` WHERE `rok` = ? AND `liga` = ? ORDER BY `kod`", $year, $league)->fetchAll();
		foreach ($result as $row) {
			$teams[] = new Team($row->id, $row->rok, $row->liga, $row->nazev_tymu, $row->kod, $row->pocet_zavodniku);
		}
		return $teams;
	}

	public function getTeamInfo(int $teamId): ArrayHash {
		$row = $this->database->query("SELECT * FROM `tymy` WHERE `id` = ?", $teamId)->fetch();
		if ($row !== false) {
			return ArrayHash::from(['teamName' => $row->nazev_tymu, 'league' => $row->liga, 'year' => (int)$row->rok]);
		}
		throw new TeamNotFoundException();
	}

	/**
	 * @return CompetitorWithCategoryAndYear[]
	 */
	public function loadMembers(int $teamId): array {
		$result = [];
		$members = $this->database->query("SELECT `z`.`id`, `z`.`cele_jmeno`, `z`.`registrace`, `z`.`registrovany`, `zk`.`kategorie`, `zk`.`rok`
			FROM `zavodnici` `z`
			LEFT JOIN `zavodnici_kategorie` `zk` ON `z`.`id` = `zk`.`id_zavodnika`
			JOIN `tymy_zavodnici` `tz` ON `tz`.`id_zavodnika` = `z`.`id`
			JOIN `tymy` `t` ON `tz`.`id_tymu` = `t`.`id`
			WHERE `tz`.`id_tymu` = ? AND `zk`.`rok` = `t`.`rok`", $teamId)->fetchAll();
		foreach ($members as $m) {
			$competitor = Competitor::fromRow($m);
			$category = Category::fromString($m->kategorie);
			$result[] = new CompetitorWithCategoryAndYear($competitor, $category, $m->rok);
		}
		return $result;
	}

	public function removeAllMembersFromTeam(int $teamId): void
	{
		$this->database->query("DELETE FROM `tymy_zavodnici` WHERE `id_tymu` = ?", $teamId);
	}

	public function addTeamMember($teamId, $memberId): void
	{
		$dbResult = $this->database->query("SELECT (MAX(`poradi`) + 1) `poradi` FROM `tymy_zavodnici` WHERE `id_tymu` = ?", (int)$teamId)->fetch();
		if ($dbResult) {
			$order = $dbResult->poradi;
			if ($order === null) $order = 1;
			$this->database->query("INSERT INTO `tymy_zavodnici`(`id_tymu`, `id_zavodnika`, `poradi`) VALUES (?, ?, ?)", $teamId, $memberId, $order);
		}
	}

	public function loadRoasterForLeague($rok, $liga): array
	{
		$result = [];
		$dbResult = $this->database->query("SELECT `id`, `nazev_tymu` FROM `tymy` WHERE `nazev_tymu` != '' AND `rok` = ? AND `liga` = ? ORDER BY `kod`", (int)$rok, $liga)->fetchAll();

		foreach ($dbResult as $row) {
			$result[$row->id] = ['name' => $row->nazev_tymu, 'members' => []];
		}

		$idZavodnici = [];

		if (count($result) > 0) {
			$dbResult = $this->database->query("SELECT `z`.`id`, `z`.`registrace`, `z`.`cele_jmeno`, `tz`.`id_tymu` FROM `zavodnici` `z` JOIN `tymy_zavodnici` `tz` ON `z`.`id` = `tz`.`id_zavodnika` WHERE `tz`.`id_tymu` IN (?)", array_keys($result));
			foreach ($dbResult as $row) {
				$result[$row->id_tymu]['members'][$row->id] = ['registration' => $row->registrace, 'name' => $row->cele_jmeno, 'category' => null];
				$idZavodnici[$row->id] = null;
			}
			if (count($idZavodnici) > 0) {
				$dbResult = $this->database->query("SELECT `id_zavodnika`, `kategorie` FROM `zavodnici_kategorie` WHERE `id_zavodnika` IN (?) AND `rok` = ?", array_keys($idZavodnici), (int)$rok);
				foreach ($dbResult as $row) {
					$idZavodnici[$row->id_zavodnika] = Category::fromString($row->kategorie);
				}
			}
		}

		foreach ($result as $idTymu => $tym) {
			foreach ($tym['members'] as $idZavodnika => $zavodnik) {
				if (isset($idZavodnici[$idZavodnika])) {
					$result[$idTymu]['members'][$idZavodnika]['category'] = $idZavodnici[$idZavodnika];
				}
			}
		}

		return $result;
	}

	public function loadTeamMembership(int $competitorId, int $year)
	{
		return $this->database->query("SELECT `t`.`id`, `t`.`liga`, `t`.`nazev_tymu`
			FROM `tymy` `t` JOIN `tymy_zavodnici` `tz` ON `t`.`id` = `tz`.`id_tymu`
			WHERE `t`.`rok` = ? AND `tz`.`id_zavodnika` = ?", $year, $competitorId)->fetchAll();
	}

	public function generateMissingTeams(int $year, string $league, int $maxCount): void {
		$range = range(1, $maxCount);
		$teams = $this->database->query("SELECT `kod` FROM `tymy` WHERE `rok` = ? AND `liga` = ?", $year, $league)->fetchPairs('kod', 'kod');
		foreach ($range as $v) {
			if (!isset($teams[$v])) {
				$this->database->query("INSERT INTO `tymy`(`rok`, `liga`, `nazev_tymu`, `kod`) VALUES (?, ?, '', ?)", $year, $league, $v);
			}
		}
	}

	public function deleteById(int $id): void {
		$this->database->query("DELETE FROM `tymy` WHERE `id` = ?", $id);
	}

	public function rename(int $id, string $name): void {
		$this->database->query("UPDATE `tymy` SET `nazev_tymu` = ? WHERE `id` = ?", $name, $id);
	}

}