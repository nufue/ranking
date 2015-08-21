<?php

namespace App\Model;

class Kategorie extends Base {

	public static $kategorie = array(
		'muz' => 'muži',
		'u14' => 'U14',
		'u18' => 'U18',
		'u23' => 'U23',
		'u12' => 'U12',
		'u12_zena' => 'U12 ženy',
		'u14_zena' => 'U14 ženy',
		'u18_zena' => 'U18 ženy',
		'u23_zena' => 'U23 ženy',
		'hendikep' => 'hendikepovaní',
		'zena' => 'ženy',
		'u10' => 'U10',
		'u10_zena' => 'U10 dívky',
		'u12' => 'U12',
		'u12_zena' => 'U12 dívky',
	);

	public static $kategorieSoupisky = array(
		'muz' => 'M',
		'u14' => 'U14',
		'u18' => 'U18',
		'u23' => 'U23',
		'u12' => 'U12',
		'u12_zena' => 'U12Ž',
		'u14_zena' => 'U14Ž',
		'u18_zena' => 'U18Ž',
		'u23_zena' => 'U23Ž',
		'hendikep' => 'H',
		'zena' => 'Ž',
		'u10' => 'U10',
		'u10_zena' => 'U10Ž',
		'u12' => 'U12',
		'u12_zena' => 'U12Ž',
	);

	public function getKategorie($rok = NULL) {
		if ($rok === NULL) $rok = self::$defaultYear;
		$query = "SELECT `id_zavodnika`, `z`.`cele_jmeno`, `kategorie` FROM `zavodnici_kategorie` `zk` JOIN `zavodnici` `z` ON `zk`.`id_zavodnika` = `z`.`id` WHERE `zk`.`rok` = ? ORDER BY `id_zavodnika`";
		$dbResult = $this->database->query($query, $rok)->fetchAll();
		return $dbResult;
	}

	public static function getKategorieForSoupisky($kategorie) {
		if (isset(self::$kategorieSoupisky[$kategorie])) return self::$kategorieSoupisky[$kategorie];
		else return '';
	}

	public function getKategorieFromString($kategorie) {
		if ($kategorie == 'M')
			$result = 'muz';
		else if ($kategorie == 'Ž' || $kategorie == 'Z')
			$result = 'zena';
		else if ($kategorie == 'U14' || $kategorie == 'U 14')
			$result = 'u14';
		else if ($kategorie == 'U18' || $kategorie == 'U 18')
			$result = 'u18';
		else if ($kategorie == 'U23' || $kategorie == 'U 23')
			$result = 'u23';
		else if ($kategorie == 'U14Ž' || $kategorie == 'U14 Ž')
			$result = 'u14_zena';
		else if ($kategorie == 'U18Ž' || $kategorie == 'U18 Ž')
			$result = 'u18_zena';
		else if ($kategorie == 'U23Ž' || $kategorie == 'U23 Ž')
			$result = 'u23_zena';
		else if ($kategorie == 'H')
			$result = 'hendikep';
		else if ($kategorie == 'U10' || $kategorie == 'U 10') 
			$result = 'u10';
		else if ($kategorie == 'U10Ž' || $kategorie == 'U10 Ž') 
			$result = 'u10_zena';
		else if ($kategorie == 'U12') 
			$result = 'u12';
		else if ($kategorie == 'U12Ž' || $kategorie == 'U12 Ž') 
			$result = 'u12_zena';
		else $result = 'fail';

		return $result;
	}

	public function addZavodnikKategorie($idZavodnika, $kategorie, $rok = NULL) {
		if ($rok === NULL) $rok = self::$defaultYear;
		$kategorie = $this->getKategorieFromString($kategorie);
		
		try {
			$this->database->table('zavodnici_kategorie')->insert(array('id_zavodnika' => $idZavodnika, 'kategorie' => $kategorie, 'rok' => $rok));
		} catch (PDOException $exc) {
			// ignorujeme - kategorie bude zrejme stejna
			// TODO kontrola
		}
		//TODO rok
	}

}