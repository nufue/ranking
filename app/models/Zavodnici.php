<?php

class Zavodnici extends Base {

	public function getZavodnici($idZavodu) {
		$dbResult = $this->database->query("SELECT z.registrace, z.cele_jmeno, zz.tym, zz.kategorie, zz.cips1, zz.umisteni1, zz.cips2, zz.umisteni2 FROM zavodnici z JOIN zavodnici_zavody zz ON z.id = zz.id_zavodnika WHERE zz.id_zavodu = ?", $idZavodu);
		return $dbResult;
	}

}