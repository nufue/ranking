<?php

class Zavodnici extends Base {

	public function getZavodnici($idZavodu) {
		$dbResult = $this->database->query("SELECT z.id `id_zavodnika`, z.registrace, z.cele_jmeno, zz.tym, zk.kategorie, zz.cips1, zz.umisteni1, zz.cips2, zz.umisteni2 FROM zavodnici z JOIN zavodnici_zavody zz ON z.id = zz.id_zavodnika JOIN zavodnici_kategorie zk ON z.id = zk.id_zavodnika WHERE zz.id_zavodu = ? ORDER BY (IF(zz.umisteni1 IS NULL, 0, 1) + IF(zz.umisteni2 IS NULL, 0, 1)) DESC, (zz.umisteni1 + zz.umisteni2), (zz.cips1 + zz.cips2) DESC", $idZavodu);
		return $dbResult;
	}

	public function getZavodnik($registrace) {
		$dbResult = $this->database->query("SELECT id, registrace, cele_jmeno FROM zavodnici LEFT JOIN zavodnici_kategorie `zk` ON id = `zk`.`id_zavodnika` WHERE registrace = ? AND rok = 2012", $registrace);
		if ($result = $dbResult->fetch()) {
			return $result;
		} else
			return NULL;
	}

	public function getZavodnikByJmeno($jmeno) {
		$dbResult = $this->database->query("SELECT id, cele_jmeno FROM zavodnici WHERE cele_jmeno = ?", $jmeno);
		if ($result = $dbResult->fetch()) {
			return $result;
		} else
			return NULL;
	}
	
	public function getZavodnikByRegistrace($registrace) {
		$dbResult = $this->database->query("SELECT id, cele_jmeno FROM zavodnici WHERE registrace = ?", $registrace);
		if ($result = $dbResult->fetch()) {
			return $result;
		} else
			return NULL;
	}

	public function getZavodnikById($id, $rok) {
		$dbResult = $this->database->query("SELECT id, registrace, cele_jmeno, kategorie FROM zavodnici z LEFT JOIN zavodnici_kategorie `zk` ON id = `zk`.`id_zavodnika` WHERE id = ? AND rok = ?", $id, $rok);
		if ($result = $dbResult->fetch()) {
			$result->kategorie = Kategorie::$kategorie[$result->kategorie];
			return $result;
		} else
			return NULL;
	}

	public function addZavodnik($registrace, $cele_jmeno, $kategorie) {
		$row = $this->database->table('zavodnici')->insert(array('registrace' => $registrace, 'cele_jmeno' => $cele_jmeno));
		$rowId = $row->id;
		$this->context->kategorie->addZavodnikKategorie($rowId, $kategorie, 2012);
		// TODO rok

		return $rowId;
	}

}