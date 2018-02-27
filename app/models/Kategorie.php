<?php

namespace App\Model;

use Nette\Database\Context;

final class Kategorie extends Base
{

	/** @var DefaultYear */
	private $defaultYear;

	public static $kategorie = [
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
		'u15' => 'U15',
		'u15_zena' => 'U15 dívky',
		'u20' => 'U20',
		'u20_zena' => 'U20 ženy',
		'u25' => 'U25',
		'u25_zena' => 'U25 ženy',
	];

	public static $kategorieSoupisky = [
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
		'u15' => 'U15',
		'u15_zena' => 'U15Ž',
		'u20' => 'U20',
		'u20_zena' => 'U20Ž',
		'u25' => 'U25',
		'u25_zena' => 'U25Ž',
	];

	public function __construct(Context $database, DefaultYear $defaultYear)
	{
		parent::__construct($database);
		$this->defaultYear = $defaultYear;
	}

	public function getCategories($year = null)
	{
		if ($year === null) $year = $this->defaultYear->getDefaultYear();
		return $this->database->query("SELECT `id_zavodnika`, `z`.`cele_jmeno`, `kategorie` FROM `zavodnici_kategorie` `zk` JOIN `zavodnici` `z` ON `zk`.`id_zavodnika` = `z`.`id` WHERE `zk`.`rok` = ? ORDER BY `id_zavodnika`", $year)->fetchAll();
	}

	public static function getKategorieForSoupisky($kategorie): string
	{
		return self::$kategorieSoupisky[$kategorie] ?? '';
	}

	public function getKategorieFromString($kategorie): string
	{
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
		else if ($kategorie == 'U15' || $kategorie == 'U 15')
			$result = 'u15';
		else if ($kategorie == 'U15Ž' || $kategorie == 'U15 Ž')
			$result = 'u15_zena';
		else if ($kategorie == 'U20' || $kategorie == 'U 20')
			$result = 'u20';
		else if ($kategorie == 'U20Ž' || $kategorie == 'U20 Ž')
			$result = 'u20_zena';
		else if ($kategorie == 'U25' || $kategorie == 'U 25')
			$result = 'u25';
		else if ($kategorie == 'U25Ž' || $kategorie == 'U25 Ž')
			$result = 'u25_zena';
		else $result = 'fail';

		return $result;
	}

	public function addCompetitorToCategory($idZavodnika, $kategorie, $year = null): void
	{
		if ($year === null) $year = $this->defaultYear->getDefaultYear();
		$kategorie = $this->getKategorieFromString($kategorie);

		try {
			$this->database->table('zavodnici_kategorie')->insert(['id_zavodnika' => $idZavodnika, 'kategorie' => $kategorie, 'rok' => $year]);
		} catch (PDOException $exc) {
			// ignorujeme - kategorie bude zrejme stejna
			// TODO kontrola
		}
		//TODO rok
	}

}