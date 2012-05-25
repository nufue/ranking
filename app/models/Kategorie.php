<?php

class Kategorie extends Base {

    public static $kategorie = array(
        'muz' => 'muži',
        'u14' => 'U14',
        'u18' => 'U18',
        'u23' => 'U23',
        'u14_zena' => 'U14 ženy',
        'u18_zena' => 'U18 ženy',
        'u23_zena' => 'U23 ženy',
        'hendikep' => 'hendikepovaní',
        'zena' => 'ženy'
    );

    public function getKategorie($rok = 2012) {
        $query = "SELECT `id_zavodnika`, `z`.`cele_jmeno`, `kategorie` FROM `zavodnici_kategorie` `zk` JOIN `zavodnici` `z` ON `zk`.`id_zavodnika` = `z`.`id` WHERE `zk`.`rok` = ? ORDER BY `id_zavodnika`";
        $dbResult = $this->database->query($query, $rok)->fetchAll();
        return $dbResult;
    }

    public function addZavodnikKategorie($idZavodnika, $kategorie, $rok = 2012) {
        if ($kategorie == 'M')
            $kategorie = 'muz';
        else if ($kategorie == 'Ž' || $kategorie == 'Z')
            $kategorie = 'zena';
        else if ($kategorie == 'U14')
            $kategorie = 'u14';
        else if ($kategorie == 'U18')
            $kategorie = 'u18';
        else if ($kategorie == 'U23')
            $kategorie = 'u23';
        else if ($kategorie == 'U14Ž' || $kategorie == 'U14 Ž')
            $kategorie == 'u14_zena';
        else if ($kategorie == 'U18Ž' || $kategorie == 'U18 Ž')
            $kategorie == 'u18_zena';
        else if ($kategorie == 'U23Ž' || $kategorie == 'U23 Ž')
            $kategorie == 'u23_zena';
        else if ($kategorie == 'H')
            $kategorie = 'hendikep';
        try {
            $this->database->table('zavodnici_kategorie')->insert(array('id_zavodnika' => $idZavodnika, 'kategorie' => $kategorie, 'rok' => $rok));
        } catch (PDOException $exc) {
            // ignorujeme - kategorie bude zrejme stejna
            // TODO kontrola
        }
        //TODO rok
    }

}