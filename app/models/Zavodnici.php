<?php

class Zavodnici extends Base {

	public function getZavodnici($idZavodu) {
		$dbResult = $this->database->query("SELECT z.id `id_zavodnika`, `z`.`registrace`, `z`.`cele_jmeno`, `z`.`registrovany`, `zz`.`tym`, `zk`.`kategorie`, `zz`.`cips1`, zz.umisteni1, zz.cips2, zz.umisteni2 FROM `zavodnici` `z` JOIN `zavodnici_zavody` `zz` ON `z`.`id` = `zz`.`id_zavodnika` JOIN `zavodnici_kategorie` `zk` ON `z`.`id` = `zk`.`id_zavodnika` JOIN `zavody` ON `zavody`.`id` = `zz`.`id_zavodu` WHERE `zz`.`id_zavodu` = ? AND `zavody`.`rok` = `zk`.`rok` ORDER BY (IF(zz.umisteni1 IS NULL, 0, 1) + IF(zz.umisteni2 IS NULL, 0, 1)) DESC, (IFNULL(zz.umisteni1, 0) + IFNULL(zz.umisteni2, 0)), (IFNULL(zz.cips1, 0) + IFNULL(zz.cips2, 0)) DESC", $idZavodu);
		return $dbResult;
	}

	public function getZavodnik($registrace, $rok) {
		$dbResult = $this->database->query("SELECT `id`, `registrace`, `cele_jmeno` FROM `zavodnici` WHERE `registrace` = ?", $registrace);
		if ($result = $dbResult->fetch()) {
			$dbResult = $this->database->query("SELECT `kategorie` FROM `zavodnici_kategorie` WHERE `id_zavodnika` = ? AND `rok` = ?", $result->id, $rok);
			if ($result2 = $dbResult->fetch()) {
				$result->kategorie = $result2->kategorie;
			} else {
				$result->kategorie = NULL;
			}
			return $result;
		} else
			return NULL;
	}

	public function getZavodnikByJmeno($jmeno) {
		$dbResult = $this->database->query("SELECT `id`, `cele_jmeno` FROM `zavodnici` WHERE `cele_jmeno` = ? AND `registrovany` = 'A'", $jmeno);
		if ($result = $dbResult->fetch()) {
			return $result;
		} else
			return NULL;
	}

	public function getZavodnikByRegistrace($registrace) {
		$dbResult = $this->database->query("SELECT `id`, `cele_jmeno` FROM `zavodnici` WHERE `registrace` = ? AND `registrovany` = 'A'", $registrace);
		if ($result = $dbResult->fetch()) {
			return $result;
		} else
			return NULL;
	}

	public function getZavodnikById($id, $rok) {
		$dbResult = $this->database->query("SELECT `id`, `registrace`, `cele_jmeno`, `kategorie` FROM `zavodnici` `z` LEFT JOIN `zavodnici_kategorie` `zk` ON `id` = `zk`.`id_zavodnika` WHERE `id` = ? AND `rok` = ?", $id, $rok);
		if ($result = $dbResult->fetch()) {
			$result->kategorie_original = $result->kategorie;
			$result->kategorie = Kategorie::$kategorie[$result->kategorie];
			return $result;
		} else
			return NULL;
	}

	public function addZavodnik($registrace, $cele_jmeno, $kategorie, $rok) {
		$row = $this->database->table('zavodnici')->insert(array('registrace' => $registrace, 'cele_jmeno' => $cele_jmeno));
		$rowId = $row->id;
		$this->context->kategorie->addZavodnikKategorie($rowId, $kategorie, $rok);
		// TODO rok

		return $rowId;
	}

	public function checkNeregistrovanyZavodnik($cele_jmeno) {
		$dbResult = $this->database->query("SELECT * FROM `zavodnici` WHERE `registrovany` = 'N' AND `cele_jmeno` = ?", $cele_jmeno);
		if ($result = $dbResult->fetch()) { /* intentionally = */
			return $result->id;
		} else {
			return NULL;
		}
	}

	public function addNeregistrovanyZavodnik($cele_jmeno, $kategorie, $rok) {
		$result = $this->checkNeregistrovanyZavodnik($cele_jmeno);
		if ($result !== NULL) {
			$dbResult = $this->database->query("SELECT `kategorie` FROM `zavodnici_kategorie` WHERE `id_zavodnika` = ? AND `rok` = ?", (int)$result, $rok)->fetch();
			if ($dbResult === FALSE) {
				$this->database->table('zavodnici_kategorie')->insert(array('id_zavodnika' => $result, 'rok' => $rok, 'kategorie' => $kategorie));
			} else if ($dbResult->kategorie == '') {
				$this->database->exec('UPDATE `zavodnici_kategorie` SET `kategorie` = ? WHERE `id_zavodnika` = ? AND `rok` = ?', $kategorie, $result, $rok);
			}
			return $result;
		} else {
			$dbResult = $this->database->query("SELECT (MAX(CAST(REPLACE(`registrace`, 'X', '') AS SIGNED) + 1)) `maximum` FROM `zavodnici` WHERE `registrovany` = 'N'");

			if ($result = $dbResult->fetch()) { /* intentionally = */
				$reg = 'X' . (int) $result->maximum;
			} else {
				$reg = 'X1';
			}

			$row = $this->database->table('zavodnici')->insert(array('registrace' => $reg, 'cele_jmeno' => $cele_jmeno, 'registrovany' => 'N'));
			$rowId = $row->id;
			$this->context->kategorie->addZavodnikKategorie($rowId, $kategorie, $rok);
			return $rowId;
		}
	}

}