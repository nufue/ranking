<?php

namespace App\Model;

use Nette\Database\Context;

class Zebricek extends Base {

	/** @var Zavody */
	private $zavody;
	
	/** @var Zavodnici */
	private $zavodnici;

	public function __construct(Context $database, Zavody $zavody, Zavodnici $zavodnici) {
		parent::__construct($database);
		$this->zavody = $zavody;
		$this->zavodnici = $zavodnici;
	}

	public static $scoringTables = [
		1 => [1 => 40, 38, 36, 34, 32, 30, 28, 26, 24, 22, 20, 18, 16, 14, 12, 10, 8, 6, 4, 2, 1],
		2 => [1 => 36, 33, 31, 29, 27, 25, 22, 19, 16, 13, 10, 7, 4, 1],
		3 => [1 => 30, 27, 25, 23, 21, 19, 16, 13, 9, 6, 3, 1],
		4 => [1 => 25, 22, 19, 16, 13, 11, 10, 8, 5, 3, 2, 1],
		5 => [1 => 15, 12, 10, 8, 6, 4, 2, 1],
	];
	public static $competitionTypes = [
		'memi_senioru' => 'MeMi ČR seniorů',
		'1_liga' => '1. liga',
		'2_liga' => '2. liga',
		'bodovany_pohar' => 'Bodovaný pohárový závod',
		'uzemni_prebor' => 'Územní přebor dospělých',
		'divize' => 'Divize',
		'micr_zeny' => 'MiČR žen',
		'micr_u14' => 'MiČR U14',
		'micr_u18' => 'MiČR U18',
		'micr_u22' => 'MiČR U23',
		'zavod_zeny' => 'Závod žen',
		'zavod_u14' => 'Závod U14',
		'zavod_u18' => 'Závod U18',
		'zavod_u22' => 'Závod U23',
		'prebor_u14' => 'Územní přebor U14',
		'prebor_u18' => 'Územní přebor U18',
		'prebor_u22' => 'Územní přebor U23',
		'prebor_u10' => 'Územní přebor U10',
		'prebor_u12' => 'Územní přebor U12',
		'zavod_u10' => 'Závod U10',
	];
	public static $competitionScoringType = [
		'memi_senioru' => 1,
		'1_liga' => 2,
		'2_liga' => 3,
		'bodovany_pohar' => 3,
		'micr_zeny' => 3,
		'micr_u14' => 3,
		'micr_u18' => 3,
		'micr_u22' => 3,
		'uzemni_prebor' => 4,
		'zavod_zeny' => 4,
		'zavod_u14' => 4,
		'zavod_u18' => 4,
		'zavod_u22' => 4,
		'zavod_u10' => 4,
		'divize' => 5,
		'prebor_u14' => 5,
		'prebor_u18' => 5,
		'prebor_u22' => 5,
		'prebor_u10' => 5,
		'prebor_u12' => 5,
	];

	public function getRanking($year, $type) {
		$competitions = [];

		$result = $this->database->query("SELECT DATE_FORMAT(MAX(`datum_do`), '%e. %c. %Y') `datum`, MAX(`datum_do`) `datum_platnosti` FROM `zavody` WHERE `rok` = ? AND zobrazovat = 'ano' AND vysledky = 'ano'", $year)->fetch();
		$datumPlatnosti = $result->datum;
		$datumPlatnostiOrig = $result->datum_platnosti;

		$result = $this->zavody->getVisibleRaces($year);
		foreach ($result as $row) {
			$competitions[$row->id] = array('typ' => $row->typ, 'nazev' => $row->nazev, 'kategorie' => $row->kategorie);
		}

		$competitors = [];
		$query = "SELECT `z`.`cele_jmeno`, `z`.`registrace`, `zz`.`id_zavodnika`, `zz`.`id_zavodu`, `zav`.`kategorie` `kategorie_zavodu`, `zz`.`tym`, `zk`.`kategorie`, `cips1`, `umisteni1`, `cips2`, `umisteni2` FROM `zavodnici_zavody` `zz` JOIN `zavodnici` `z` ON `zz`.`id_zavodnika` = `z`.`id` JOIN `zavody` `zav` ON `zz`.`id_zavodu` = `zav`.`id` JOIN `zavodnici_kategorie` `zk` ON `zz`.`id_zavodnika` = `zk`.`id_zavodnika` WHERE `zk`.`rok` = `zav`.`rok` AND `z`.`registrovany` = 'A' AND (`cips1` IS NOT NULL OR `cips2` IS NOT NULL) AND (`zav`.`zobrazovat` = 'ano') AND (`zav`.`vysledky` = 'ano') AND `zav`.`rok` = ? ";
		if ($type == 'u23')
			$query .= " AND `zk`.`kategorie` IN ('u23', 'u23_zena') AND `zav`.`kategorie` != 'ženy'";
		else if ($type == 'u18')
			$query .= " AND `zk`.`kategorie` IN ('u18', 'u18_zena') AND `zav`.`kategorie` != 'ženy'";
		else if ($type == 'u14')
			$query .= " AND `zk`.`kategorie` IN ('u14', 'u14_zena') AND `zav`.`kategorie` != 'ženy'";
		else if ($type == 'u10')
			$query .= " AND `zk`.`kategorie` IN ('u10', 'u10_zena') AND `zav`.`kategorie` != 'ženy'";
		else if ($type == 'zeny')
			$query .= " AND `zk`.`kategorie` IN ('u10_zena', 'u14_zena', 'u18_zena', 'u23_zena', 'zena', 'u12_zena') AND (`zav`.`kategorie` = '' OR `zav`.`kategorie` = 'ženy')";
		else if ($type == 'u12')
			$query .= " AND `zk`.`kategorie` IN ('u12', 'u12_zena') AND `zav`.`kategorie` != 'ženy'";
		else if ($type == 'excel')
			;

		$result = $this->database->query($query . " ORDER BY `zav`.`datum_od`, `zav`.`nazev`", $year);
		foreach ($result as $row) {
			$id = $row->id_zavodnika;
			if (!isset($competitors[$id]))
				$competitors[$id] = array('jmeno' => $row->cele_jmeno, 'min_body_zebricek' => 0, 'zavodu' => 0, 'registrace' => $row->registrace, 'tym' => $row->tym, 'kategorie' => Kategorie::$kategorie[$row->kategorie], 'body_celkem' => array(), 'body_zebricek' => array(), 'cips_celkem' => array(), 'vysledky' => array($row->id_zavodu => array('zavod' => $row->id_zavodu, 'kategorie_zavodu' => $row->kategorie_zavodu, 'cips1' => $row->cips1, 'cips2' => $row->cips2, 'umisteni1' => $row->umisteni1, 'umisteni2' => $row->umisteni2, 'body' => 0)));
			else {
				$competitors[$id]['vysledky'][$row->id_zavodu] = array('zavod' => $row->id_zavodu, 'kategorie_zavodu' => $row->kategorie_zavodu, 'umisteni1' => $row->umisteni1, 'umisteni2' => $row->umisteni2, 'cips1' => $row->cips1, 'cips2' => $row->cips2, 'body' => 0);
			}
			if ($row->umisteni1 !== NULL)
				$competitors[$id]['zavodu'] ++;
			if ($row->umisteni2 !== NULL)
				$competitors[$id]['zavodu'] ++;
		}

		if (count($competitors) > 0) {
			$result = $this->database->query("SELECT `tz`.`id_zavodnika`, `t`.`nazev_tymu`, 
(select min(poradi) from `tymy_zavodnici` `tz2` WHERE `tz2`.`id_tymu` = `tz`.`id_tymu` AND `tz2`.`id_zavodnika` = `tz`.`id_zavodnika`) `pocet`,
(select count(*) from `tymy_zavodnici` `tz2` WHERE `tz2`.`id_tymu` = `tz`.`id_tymu`) `procento`
FROM `tymy_zavodnici` `tz` JOIN `tymy` `t` ON `tz`.`id_tymu` = `t`.`id` WHERE `tz`.`id_zavodnika` IN (?) AND `rok` = ? ORDER BY id_zavodnika, `pocet` / `procento` DESC", array_keys($competitors), $year);
			foreach ($result as $row) {
				$competitors[$row->id_zavodnika]['tym'] = $row->nazev_tymu;
			}
		}

		foreach ($competitors as $id => $z) {
			foreach ($z['vysledky'] as $k => $v) {
				$idZavodu = $v['zavod'];
				$kategorieZavodu = $v['kategorie_zavodu'];
				$typZavodu = $competitions[$idZavodu]['typ'];

				$body1 = $this->getPoints($typZavodu, $v['umisteni1']);
				$body2 = $this->getPoints($typZavodu, $v['umisteni2']);
				$cips1 = $v['cips1'];
				$cips2 = $v['cips2'];

				if ($v['umisteni1'] !== NULL) {
					$competitors[$id]['vysledky'][$k]['body1'] = $body1;
					$competitors[$id]['vysledky'][$k]['body1_zebricek'] = FALSE;
					$competitors[$id]['body_celkem'][] = $body1;
					if ($type != 'celkem' || $kategorieZavodu == '')
						$competitors[$id]['body_zebricek'][] = $body1;
					$competitors[$id]['cips_celkem'][] = $cips1;
				} else {
					$competitors[$id]['vysledky'][$k]['body1'] = NULL;
					$competitors[$id]['vysledky'][$k]['body1_zebricek'] = FALSE;
				}
				if ($v['umisteni2'] !== NULL) {
					$competitors[$id]['vysledky'][$k]['body2'] = $body2;
					$competitors[$id]['vysledky'][$k]['body2_zebricek'] = FALSE;
					$competitors[$id]['body_celkem'][] = $body2;
					if ($type != 'celkem' || $kategorieZavodu == '')
						$competitors[$id]['body_zebricek'][] = $body2;
					$competitors[$id]['cips_celkem'][] = $cips2;
				} else {
					$competitors[$id]['vysledky'][$k]['body2'] = NULL;
					$competitors[$id]['vysledky'][$k]['body2_zebricek'] = FALSE;
				}
			}
		}
		foreach ($competitors as $id => $z) {
			if (count($competitors[$id]['body_zebricek']) > 12) {
				rsort($competitors[$id]['body_zebricek']);
				$competitors[$id]['body_zebricek'] = array_slice($competitors[$id]['body_zebricek'], 0, 12);
			}
			if (count($competitors[$id]['body_zebricek']) > 11) {
				$temp = array_values($competitors[$id]['body_zebricek']);
				rsort($temp);
				$competitors[$id]['min_body_zebricek'] = array_pop($temp) + 1;
			} else {
				$competitors[$id]['min_body_zebricek'] = 0;
			}

			$bodyZebricekKopie = $competitors[$id]['body_zebricek'];
			foreach ($z['vysledky'] as $k => $v) {
				$body1 = $competitors[$id]['vysledky'][$k]['body1'];
				if ($body1 !== NULL) {
					$ind = array_search($body1, $bodyZebricekKopie);
					if ($ind !== FALSE) {
						$competitors[$id]['vysledky'][$k]['body1_zebricek'] = TRUE;
						unset($bodyZebricekKopie[$ind]);
					}
				}
				$body2 = $competitors[$id]['vysledky'][$k]['body2'];
				if ($body2 !== NULL) {
					$ind = array_search($body2, $bodyZebricekKopie);
					if ($ind !== FALSE) {
						$competitors[$id]['vysledky'][$k]['body2_zebricek'] = TRUE;
						unset($bodyZebricekKopie[$ind]);
					}
				}
			}
		}

		uasort($competitors, array($this, 'bodySort'));
		return array('zavody' => $competitions, 'zavodnici' => $competitors, 'datum_platnosti' => $datumPlatnosti, 'datum_platnosti_orig' => $datumPlatnostiOrig);
	}

	private function getPoints($competitionType, $position) {
		$bodovaciTabulka = self::$scoringTables[self::$competitionScoringType[$competitionType]];
		if ($position === NULL)
			return NULL;
		$position = (int) $position;
		if (isset($bodovaciTabulka[$position]))
			$body = $bodovaciTabulka[$position];
		else
			$body = 0;
		return $body;
	}

	public function getVysledkyZavodu($idZavodnika, $rok, $omezeni = NULL) {
		$zavodnik = [];
		$headerSet = FALSE;

		$query = "SELECT
					`zav`.`id` `id_zavodu`, `zav`.`nazev` `nazev_zavodu`, `zav`.`typ` `typ`, `zav`.`kategorie` `kategorie_zavodu`, `zz`.`tym`, `zk`.`kategorie`, `cips1`, `umisteni1`, `cips2`, `umisteni2`, `z`.`registrovany`
					FROM `zavodnici_zavody` `zz`
					JOIN `zavodnici` `z` ON `zz`.`id_zavodnika` = `z`.`id`
					JOIN `zavody` `zav` ON `zz`.`id_zavodu` = `zav`.`id`
					JOIN `zavodnici_kategorie` `zk` ON `zz`.`id_zavodnika` = `zk`.`id_zavodnika`
					WHERE `zav`.`rok` = `zk`.`rok`
					AND (`cips1` IS NOT NULL OR `cips2` IS NOT NULL)
					AND (`zav`.`zobrazovat` = 'ano')
					AND (`zav`.`vysledky` = 'ano')
					AND `z`.`id` = ? AND `zav`.`rok` = ?";

		if ($omezeni === NULL)
			$query .= " AND `zav`.`kategorie` = ''";
		if ($omezeni == 'ženy')
			$query .= " AND `zk`.`kategorie` IN ('u10_zena', 'u14_zena', 'u18_zena', 'u23_zena', 'zena', 'u12_zena') AND (`zav`.`kategorie` = '' OR `zav`.`kategorie` = 'ženy')";
		if ($omezeni[0] == 'u')
			$query .= " AND `zk`.`kategorie` IN ('" . $omezeni . "', '" . $omezeni . "_zena') AND `zav`.`kategorie` != 'ženy'";

		$query .= " ORDER BY `zav`.`datum_od`, `zav`.`nazev`";

		$result = $this->database->query($query, $idZavodnika, $rok);

		foreach ($result as $row) {
			if (!$headerSet) {
				$zavodnik = array('zavodu' => 0, 'registrovany' => ($row->registrovany == 'A'), 'body_celkem' => array(), 'vysledky' => array($row->id_zavodu => array('nazev_zavodu' => $row->nazev_zavodu, 'tym' => $row['tym'], 'typ_zavodu' => $row->typ, 'kategorie_zavodu' => $row->kategorie_zavodu, 'id_zavodu' => $row->id_zavodu, 'umisteni1' => $row->umisteni1, 'umisteni2' => $row->umisteni2, 'cips1' => $row->cips1, 'cips2' => $row->cips2)));
				$headerSet = true;
			} else {
				$zavodnik['vysledky'][$row->id_zavodu] = array('nazev_zavodu' => $row->nazev_zavodu, 'tym' => $row['tym'], 'typ_zavodu' => $row->typ, 'kategorie_zavodu' => $row->kategorie_zavodu, 'id_zavodu' => $row->id_zavodu, 'umisteni1' => $row->umisteni1, 'umisteni2' => $row->umisteni2, 'cips1' => $row->cips1, 'cips2' => $row->cips2);
			}
			if ($row->umisteni1 !== NULL)
				$zavodnik['zavodu'] ++;
			if ($row->umisteni2 !== NULL)
				$zavodnik['zavodu'] ++;
		}

		if (isset($zavodnik['vysledky'])) {
			foreach ($zavodnik['vysledky'] as $k => $v) {
				$typZavodu = $v['typ_zavodu'];

				$body1 = $this->getPoints($typZavodu, $v['umisteni1']);
				$body2 = $this->getPoints($typZavodu, $v['umisteni2']);

				if ($v['umisteni1'] !== NULL) {
					$zavodnik['vysledky'][$k]['body1'] = $body1;
					$zavodnik['vysledky'][$k]['body1_zebricek'] = false;
					$zavodnik['vysledky'][$k]['cips1'] = $v['cips1'];
					$zavodnik['body_celkem'][] = $body1;
					$zavodnik['cips_celkem'][] = $v['cips1'];
				} else {
					$zavodnik['vysledky'][$k]['body1'] = NULL;
					$zavodnik['vysledky'][$k]['body1_zebricek'] = false;
				}
				if ($v['umisteni2'] !== NULL) {
					$zavodnik['vysledky'][$k]['body2'] = $body2;
					$zavodnik['vysledky'][$k]['cips2'] = $v['cips2'];
					$zavodnik['vysledky'][$k]['body2_zebricek'] = false;
					$zavodnik['body_celkem'][] = $body2;
					$zavodnik['cips_celkem'][] = $v['cips2'];
				} else {
					$zavodnik['vysledky'][$k]['body2'] = NULL;
					$zavodnik['vysledky'][$k]['body2_zebricek'] = false;
				}
			}
			$zavodnik['body_zebricek'] = $zavodnik['body_celkem'];
			if (count($zavodnik['body_zebricek']) > 12) {
				rsort($zavodnik['body_zebricek']);
				$zavodnik['body_zebricek'] = array_slice($zavodnik['body_zebricek'], 0, 12);
			}

			$bodyZebricekKopie = $zavodnik['body_zebricek'];
			foreach ($zavodnik['vysledky'] as $k => $v) {
				$body1 = $zavodnik['vysledky'][$k]['body1'];
				if ($body1 !== NULL) {
					$ind = array_search($body1, $bodyZebricekKopie);
					if ($ind !== false) {
						$zavodnik['vysledky'][$k]['body1_zebricek'] = true;
						unset($bodyZebricekKopie[$ind]);
					}
				}
				$body2 = $zavodnik['vysledky'][$k]['body2'];
				if ($body2 !== NULL) {
					$ind = array_search($body2, $bodyZebricekKopie);
					if ($ind !== false) {
						$zavodnik['vysledky'][$k]['body2_zebricek'] = true;
						unset($bodyZebricekKopie[$ind]);
					}
				}
			}
		}

		return $zavodnik;
	}

	public function getZavodnikRok($idZavodnika, $rok) {
		$zavodnik = $this->zavodnici->getZavodnikById($idZavodnika, $rok);
		$vysledky = $this->getVysledkyZavodu($idZavodnika, $rok, 'všechny');

		$vysledkyDorost = NULL;
		$vysledkyZeny = NULL;
		$vysledkyCelkovy = $this->getVysledkyZavodu($idZavodnika, $rok, NULL);

		if (isset($zavodnik->kategorie_original) && (substr($zavodnik->kategorie_original, -5, 5) == '_zena' || $zavodnik->kategorie_original == 'zena')) {
			$vysledkyZeny = $this->getVysledkyZavodu($idZavodnika, $rok, 'ženy');
		}

		if (isset($zavodnik->kategorie_original) && substr($zavodnik->kategorie_original, 0, 1) == 'u') {
			$vysledkyDorost = $this->getVysledkyZavodu($idZavodnika, $rok, substr($zavodnik->kategorie_original, 0, 3));
		}

		return array('zavodnik' => $zavodnik, 'vysledky' => (isset($vysledky['vysledky']) ? $vysledky['vysledky'] : NULL), 'vysledky_celkovy' => $vysledkyCelkovy, 'vysledky_zeny' => $vysledkyZeny, 'vysledky_dorost' => $vysledkyDorost);
	}

	private function bodySort($a, $b) {
		$sumA = array_sum($a['body_zebricek']);
		$sumB = array_sum($b['body_zebricek']);
		$sumCips1 = array_sum($a['cips_celkem']);
		$sumCips2 = array_sum($b['cips_celkem']);

		if ($sumA == $sumB) {
			if ($a['zavodu'] < $b['zavodu'])
				return -1;
			else if ($a['zavodu'] > $b['zavodu'])
				return 1;
			else {
				if ($sumCips1 > $sumCips2) {
					return -1;
				} else if ($sumCips1 < $sumCips2) {
					return 1;
				} else {
					return 0;
				}
			}
		} else if ($sumA > $sumB) {
			return -1;
		} else {
			return 1;
		}
	}

}
