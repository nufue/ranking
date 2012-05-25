<?php

class Zavodnici extends Base {

    public function getZavodnici($idZavodu) {
        $dbResult = $this->database->query("SELECT z.registrace, z.cele_jmeno, zz.tym, zk.kategorie, zz.cips1, zz.umisteni1, zz.cips2, zz.umisteni2 FROM zavodnici z JOIN zavodnici_zavody zz ON z.id = zz.id_zavodnika JOIN zavodnici_kategorie zk ON z.id = zk.id_zavodnika WHERE zz.id_zavodu = ?", $idZavodu);
        return $dbResult;
    }

    public function getZavodnik($registrace) {
        $dbResult = $this->database->query("SELECT id, registrace, cele_jmeno FROM zavodnici LEFT JOIN zavodnici_kategorie `zk` ON id = `zk`.`id_zavodnika` WHERE registrace = ? AND rok = 2012", $registrace);
        if ($result = $dbResult->fetch()) {
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