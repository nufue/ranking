<?php

class Zavody extends Base {

    public function getZavody($rok) {
        $dbResult = $this->database->query($query = "SELECT * FROM zavody WHERE `rok` = ? ORDER BY `datum_od`, `nazev`", $rok);
        return $dbResult;
    }

    public function getZavod($id) {
        $dbResult = $this->database->query($query = "SELECT `id`, `nazev`, `typ`, `datum_od`, `datum_do`, `zobrazovat`, `vysledky` FROM zavody WHERE `id` = ?", $id)->fetch();
        return $dbResult;
    }

    public function updateZavod($id, $values) {
        $this->database->query("UPDATE zavody SET `nazev` = ?, `typ` = ?, `datum_od` = ?, `datum_do` = ?, `zobrazovat` = ?, `vysledky` = ? WHERE `id`= ? ", $values['nazev'], $values['typ'], $values['datum_od'], $values['datum_do'], $values['zobrazovat'], $values['vysledky'], $id);
    }

    public function addZavod($values) {
        $this->database->query("INSERT INTO zavody(nazev, typ, rok, datum_od, datum_do, zobrazovat, vysledky) VALUES (?, ?, 2012, ?, ?, ?, ?)", $values['nazev'], $values['typ'], $values['datum_od'], $values['datum_do'], $values['zobrazovat'], $values['vysledky']);
    }

}