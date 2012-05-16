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

}