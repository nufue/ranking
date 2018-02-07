<?php

namespace App\Model;

final class Teams extends Base {

	public function getTymy() {
		$query = "SELECT `zz`.`id_zavodnika`, `zz`.`id_zavodu`, `z`.`cele_jmeno`, `zz`.`tym` FROM `zavodnici` `z` JOIN `zavodnici_zavody` `zz` ON `z`.`id` = `zz`.`id_zavodnika` WHERE `id_zavodnika` IN (SELECT DISTINCT `id_zavodnika` FROM `zavodnici_zavody` GROUP BY `id_zavodnika` HAVING COUNT(`id_zavodnika`) > 1) ORDER BY `id_zavodnika`";
		$dbResult = $this->database->query($query)->fetchAll();
		$result = [];
		foreach ($dbResult as $row) {
			if (!isset($result[$row->id_zavodnika])) {
				$result[$row->id_zavodnika] = ['jmeno' => $row->cele_jmeno, 'zavody' => [['id' => $row->id_zavodu, 'tym' => $row->tym]]];
			} else {
				$found = FALSE;
				foreach ($result[$row->id_zavodnika]['zavody'] as $v) {
					if ($v['tym'] == $row->tym) {
						$found = TRUE;
						break;
					}
				}
				if (!$found) {
					$result[$row->id_zavodnika]['zavody'][] = ['id' => $row->id_zavodu, 'tym' => $row->tym];
				}
			}
		}
		foreach ($result as $k => $v) {
			if (count($v['zavody']) === 1)
				unset($result[$k]);
		}
		return $result;
	}

	public function loadTeamsByYear($rok) {
		return $this->database->query("SELECT t.*, count(tz.id_zavodnika) `pocet_zavodniku` FROM tymy t LEFT JOIN tymy_zavodnici tz ON t.id = tz.id_tymu WHERE rok = ? GROUP BY `id` ORDER BY `liga`, `kod`", $rok);
	}

	public function getById($id) {
		$info = $this->database->query("SELECT * FROM tymy WHERE id = ?", $id)->fetch();
		$zavodnici = $this->database->query("SELECT `z`.`id`, `z`.`cele_jmeno`, `z`.`registrace`, `zk`.`kategorie` FROM `zavodnici` `z` LEFT JOIN `zavodnici_kategorie` `zk` ON `z`.`id` = `zk`.`id_zavodnika` JOIN `tymy_zavodnici` `tz` ON `tz`.`id_zavodnika` = `z`.`id` WHERE `tz`.`id_tymu` = ? AND `zk`.`rok` = ?", $id, $info->rok)->fetchAll();
		return ['info' => $info, 'zavodnici' => $zavodnici];
	}

	public function removeAllMemberFromTeam($teamId): void {
		$this->database->query("DELETE FROM tymy_zavodnici WHERE id_tymu = ?", $teamId);
	}

	public function addTeamMember($teamId, $memberId): void {
		$dbResult = $this->database->query("SELECT (MAX(`poradi`) + 1) `poradi` FROM `tymy_zavodnici` WHERE `id_tymu` = ?", (int)$teamId)->fetch();
		if ($dbResult) {
			$order = $dbResult->poradi;
			if ($order === NULL) $order = 1;
			$this->database->query("INSERT INTO `tymy_zavodnici`(`id_tymu`, `id_zavodnika`, `poradi`) VALUES (?, ?, ?)", $teamId, $memberId, $order);
		}
	}

	public function loadRoasterForLeague($rok, $liga): array {
		$result = [];
		$dbResult = $this->database->query("SELECT * FROM `tymy` WHERE `rok` = ? AND `liga` = ? ORDER BY `kod`", (int)$rok, $liga)->fetchAll();

		foreach ($dbResult as $row) {
			$result[$row->id] = ['nazev' => $row->nazev_tymu, 'clenove' => []];
		}

		$dbResult = $this->database->query("SELECT z.*, tz.id_tymu FROM zavodnici z JOIN tymy_zavodnici tz ON z.id = tz.id_zavodnika WHERE tz.id_tymu IN (?)", array_keys($result));

		$idZavodnici = [];
		foreach ($dbResult as $row) {
			$result[$row->id_tymu]['clenove'][$row->id] = ['registrace' => $row->registrace, 'jmeno' => $row->cele_jmeno, 'kategorie' => NULL];
			$idZavodnici[$row->id] = NULL;
		}

		if (count($idZavodnici) > 0) {
			$dbResult = $this->database->query("SELECT `id_zavodnika`, `kategorie` FROM `zavodnici_kategorie` WHERE `id_zavodnika` IN (?) AND `rok` = ?", array_keys($idZavodnici), (int)$rok);

			foreach ($dbResult as $row) {
				$idZavodnici[$row->id_zavodnika] = $row->kategorie;
			}
		}

		foreach ($result as $idTymu => $tym) {
			foreach ($tym['clenove'] as $idZavodnika => $zavodnik) {
				if (isset($idZavodnici[$idZavodnika])) {
					$result[$idTymu]['clenove'][$idZavodnika]['kategorie'] = $idZavodnici[$idZavodnika];
				} 
			}
		}
		
		return $result;
	}
	
	public function loadTeamMembership($idZavodnika, $rok) {
		return $this->database->query("SELECT t.id, t.liga, t.nazev_tymu FROM tymy t JOIN tymy_zavodnici tz ON t.id = tz.id_tymu WHERE t.rok = ? AND tz.id_zavodnika = ?", $rok, $idZavodnika)->fetchAll();
	}

}