<?php

namespace App\Model;

use Nette\Database\Context;

final class Zavodnici extends Base
{

	/** @var Kategorie */
	private $categories;

	public function __construct(Context $database, Kategorie $categories)
	{
		parent::__construct($database);
		$this->categories = $categories;
	}

	public function getZavodnici($idCompetition)
	{
		return $this->database->query("SELECT z.id `id_zavodnika`, `z`.`registrace`, `z`.`cele_jmeno`, `z`.`registrovany`, `zz`.`tym`, `zk`.`kategorie`, `zz`.`cips1`, zz.umisteni1, zz.cips2, zz.umisteni2 FROM `zavodnici` `z` JOIN `zavodnici_zavody` `zz` ON `z`.`id` = `zz`.`id_zavodnika` JOIN `zavodnici_kategorie` `zk` ON `z`.`id` = `zk`.`id_zavodnika` JOIN `zavody` ON `zavody`.`id` = `zz`.`id_zavodu` WHERE `zz`.`id_zavodu` = ? AND `zavody`.`rok` = `zk`.`rok` ORDER BY (IF(zz.umisteni1 IS NULL, 0, 1) + IF(zz.umisteni2 IS NULL, 0, 1)) DESC, (IFNULL(zz.umisteni1, 0) + IFNULL(zz.umisteni2, 0)), (IFNULL(zz.cips1, 0) + IFNULL(zz.cips2, 0)) DESC", $idCompetition);
	}

	public function getZavodnik($registrace, $year)
	{
		$dbResult = $this->database->query("SELECT `id`, `registrace`, `cele_jmeno` FROM `zavodnici` WHERE `registrace` = ?", $registrace);
		if ($result = $dbResult->fetch()) {
			$dbResult = $this->database->query("SELECT `kategorie` FROM `zavodnici_kategorie` WHERE `id_zavodnika` = ? AND `rok` = ?", $result->id, $year);
			if ($result2 = $dbResult->fetch()) {
				$result->kategorie = $result2->kategorie;
			} else {
				$result->kategorie = null;
			}
			return $result;
		} else
			return null;
	}

	public function getZavodnikByJmeno($name)
	{
		$dbResult = $this->database->query("SELECT `id`, `cele_jmeno`, `registrace` FROM `zavodnici` WHERE `cele_jmeno` = ? AND `registrovany` = 'A'", $name);
		if ($result = $dbResult->fetch()) {
			return $result;
		} else
			return null;
	}

	public function getZavodnikByRegistrace($registrace)
	{
		$dbResult = $this->database->query("SELECT `id`, `cele_jmeno`, `registrace` FROM `zavodnici` WHERE `registrace` = ? AND `registrovany` = 'A'", $registrace);
		if ($result = $dbResult->fetch()) {
			return $result;
		} else
			return null;
	}

	public function getZavodnikById($id, $year)
	{
		$dbResult = $this->database->query("SELECT `id`, `registrace`, `cele_jmeno`, `kategorie` FROM `zavodnici` `z` LEFT JOIN `zavodnici_kategorie` `zk` ON `id` = `zk`.`id_zavodnika` WHERE `id` = ? AND `rok` = ?", $id, $year);
		if ($result = $dbResult->fetch()) {
			$result->kategorie_original = $result->kategorie;
			$result->kategorie = Kategorie::$kategorie[$result->kategorie];
			return $result;
		} else
			return null;
	}

	public function addCompetitor($registrationNumber, $fullName, $category, $year)
	{
		$row = $this->database->table('zavodnici')->insert(['registrace' => $registrationNumber, 'cele_jmeno' => $fullName]);
		$rowId = $row->id;
		$this->categories->addCompetitorToCategory($rowId, $category, $year);
		// TODO rok
		return $rowId;
	}

	public function isExistingUnregistered($fullName)
	{
		return $this->database->query("SELECT `id` FROM `zavodnici` WHERE `registrovany` = 'N' AND `cele_jmeno` = ?", $fullName)->fetchField();
	}

	public function getUnregisteredCategory($id, $year)
	{
		return $this->database->query("SELECT `kategorie` FROM `zavodnici_kategorie` WHERE `id_zavodnika` = ? AND `rok` = ?", $id, $year)->fetchField();
	}

	public function addUnregistered($fullName, $category, $year)
	{
		$result = $this->isExistingUnregistered($fullName);
		if ($result !== false) {
			$dbCategory = $this->database->query("SELECT `kategorie` FROM `zavodnici_kategorie` WHERE `id_zavodnika` = ? AND `rok` = ?", (int)$result, $year)->fetch();
			if ($dbCategory === false) {
				$this->database->table('zavodnici_kategorie')->insert(['id_zavodnika' => $result, 'rok' => $year, 'kategorie' => $this->kategorieConvertToDB($category)]);
			} else if ($dbCategory->kategorie == '') {
				$this->database->query('UPDATE `zavodnici_kategorie` SET `kategorie` = ? WHERE `id_zavodnika` = ? AND `rok` = ?', $this->kategorieConvertToDB($category), $result, $year);
			}
			return $result;
		} else {
			$newMaximum = $this->database->query("SELECT IFNULL(MAX(CAST(REPLACE(`registrace`, 'X', '') AS SIGNED) + 1), 1) `maximum` FROM `zavodnici` WHERE `registrovany` = 'N'")->fetchField();
			$row = $this->database->table('zavodnici')->insert(['registrace' => 'X' . $newMaximum, 'cele_jmeno' => $fullName, 'registrovany' => 'N']);
			$rowId = $row->id;
			$this->categories->addCompetitorToCategory($rowId, $category, $year);
			return $rowId;
		}
	}

	private function kategorieConvertToDB($kategorie): string
	{
		$kategorie = str_replace(' ', '', $kategorie);
		if ($kategorie == 'U14Ž') return 'u14_zena';
		else if ($kategorie == 'U18Ž') return 'u18_zena';
		else if ($kategorie == 'U23Ž') return 'u23_zena';
		else if ($kategorie == 'U10Ž') return 'u10_zena';
		else if ($kategorie == 'U12Ž') return 'u12_zena';
		else if ($kategorie == 'H') return 'hendikep';
		else if ($kategorie == 'M') return 'muz';
		else if ($kategorie == 'Z' || $kategorie == 'Ž') return 'zena';
		else if ($kategorie == 'U14') return 'u14';
		else if ($kategorie == 'U18') return 'u18';
		else if ($kategorie == 'U23') return 'u23';
		else if ($kategorie == 'U10') return 'u10';
		else if ($kategorie == 'U12') return 'u12';
		else if ($kategorie == 'U15') return 'u15';
		else if ($kategorie == 'U20') return 'u20';
		else if ($kategorie == 'U25') return 'u25';
		else if ($kategorie == 'U15Ž') return 'u15_zena';
		else if ($kategorie == 'U20Ž') return 'u20_zena';
		else if ($kategorie == 'U25Ž') return 'u25_zena';
		else return $kategorie;
	}

	public function loadCompetitorsWithoutCategory()
	{
		$result = [];
		$dbResult = $this->database->query("select tz.id_zavodnika, (select kategorie from zavodnici_kategorie zk where zk.rok = 2017 and zk.id_zavodnika = tz.id_zavodnika) kategorie from tymy_zavodnici tz join tymy t on tz.id_tymu = t.id where t.rok = 2017 having kategorie IS NULL")->fetchAll();
		foreach ($dbResult as $r)
			$result[] = $r->id_zavodnika;
		return $result;
	}

	public function loadAllCompetitors()
	{
		return $this->database->query("select tz.id_zavodnika, (select kategorie from zavodnici_kategorie zk where zk.rok = 2017 and zk.id_zavodnika = tz.id_zavodnika) kategorie from tymy_zavodnici tz join tymy t on tz.id_tymu = t.id where t.rok = 2017")->fetchAll();
	}

}
