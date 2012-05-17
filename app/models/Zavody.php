<?php

class Zavody extends Base {

	public function getZavody($rok) {
		$dbResult = $this->database->query($query = "SELECT * FROM zavody WHERE `rok` = ? ORDER BY `datum_od`", $rok);
		return $dbResult;
	}

	public function getZavod($id) {
		$dbResult = $this->database->query($query = "SELECT `id`, `nazev`, `typ`, `datum_od`, `datum_do` FROM zavody WHERE `id` = ?", $id)->fetch();
		return $dbResult;
	}

}