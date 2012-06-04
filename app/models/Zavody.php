<?php

class Zavody extends Base {

	public function getZavody($rok, $vsechny = false) {
		if ($vsechny)
			$dbResult = $this->database->query($query = "SELECT * FROM zavody WHERE `rok` = ? ORDER BY `datum_od`, `nazev`", $rok);
		else
			$dbResult = $this->database->query($query = "SELECT * FROM zavody WHERE `rok` = ? AND `zobrazovat` = 'ano' AND `vysledky` = 'ano' ORDER BY `datum_od`, `nazev`", $rok);

		return $dbResult;
	}

	public function getZavod($id) {
		$dbResult = $this->database->query($query = "SELECT `id`, `nazev`, `typ`, `datum_od`, `datum_do`, `zobrazovat`, `vysledky`, `kategorie` FROM zavody WHERE `id` = ?", $id)->fetch();
		return $dbResult;
	}

	public function updateZavod($id, $values) {
		$this->database->query("UPDATE zavody SET `nazev` = ?, `typ` = ?, `datum_od` = ?, `datum_do` = ?, `zobrazovat` = ?, `vysledky` = ? WHERE `id`= ? ", $values['nazev'], $values['typ'], $values['datum_od'], $values['datum_do'], $values['zobrazovat'], $values['vysledky'], $id);
	}

	public function addZavod($values) {
		$this->database->query("INSERT INTO zavody(nazev, kategorie, typ, rok, datum_od, datum_do, zobrazovat, vysledky) VALUES (?, ?, ?, 2012, ?, ?, ?, ?)", $values['nazev'], $values['kategorie'], $values['typ'], $values['datum_od'], $values['datum_do'], $values['zobrazovat'], $values['vysledky']);
	}

	public function addVysledek($idZavodu, $idZavodnika, $tym, $cips1, $umisteni1, $cips2, $umisteni2) {
		$this->database->table('zavodnici_zavody')->insert(array('id_zavodu' => $idZavodu, 'id_zavodnika' => $idZavodnika, 'tym' => $tym, 'cips1' => $cips1, 'umisteni1' => $umisteni1, 'cips2' => $cips2, 'umisteni2' => $umisteni2));
	}

	public function deleteVysledky($idZavodu) {
		$this->database->query('DELETE FROM zavodnici_zavody WHERE id_zavodu = ?', $idZavodu);
		$this->database->query('UPDATE zavody SET vysledky = ? WHERE id = ?', 'ne', $idZavodu);
	}

	public function confirmAddVysledek($idZavodu) {
		$this->database->query('UPDATE zavody SET vysledky = ? WHERE id = ?', 'ano', $idZavodu);
	}

}