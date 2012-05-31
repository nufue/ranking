<?php

class Zebricek extends Base {

	public static $bodovaci_tabulky = array(
		1 => array(1 => 40, 38, 36, 34, 32, 30, 28, 26, 24, 22, 20, 18, 16, 14, 12, 10, 8, 6, 4, 2, 1),
		2 => array(1 => 36, 33, 31, 29, 27, 25, 22, 19, 16, 13, 10, 7, 4, 1),
		3 => array(1 => 30, 27, 25, 23, 21, 19, 16, 13, 9, 6, 3, 1),
		4 => array(1 => 25, 22, 19, 16, 13, 11, 10, 8, 5, 3, 2, 1),
		5 => array(1 => 15, 12, 10, 8, 6, 4, 2, 1),
	);
	public static $typyZavodu = array(
		'memi_senioru' => 'MeMi ČR seniorů',
		'1_liga' => '1. liga',
		'2_liga' => '2. liga',
		'bodovany_pohar' => 'Bodovaný pohárový závod',
		'uzemni_prebor' => 'Územní přebor dospělých',
		'divize' => 'Divize',
		'micr_zeny' => 'MiČR žen',
		'micr_u14' => 'MiČR U14',
		'micr_u18' => 'MiČR U18',
		'micr_u22' => 'MiČR U22',
		'zavod_zeny' => 'Závod žen',
		'zavod_u14' => 'Závod U14',
		'zavod_u18' => 'Závod U18',
		'zavod_u22' => 'Závod U22',
		'prebor_u14' => 'Územní přebor U14',
		'prebor_u18' => 'Územní přebor U18',
		'prebor_u22' => 'Územní přebor U22',
	);
	public static $bodovani_zavodu = array(
		'memicr' => 1,
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
		'divize' => 5,
		'prebor_u14' => 5,
		'prebor_u18' => 5,
		'prebor_u22' => 5,
	);

	public function getZebricek($rok, $typ) {
		$zavody = array();

		$result = $this->context->database->query("SELECT DATE_FORMAT(MAX(`datum_do`), '%e. %c. %Y') `datum`, MAX(`datum_do`) `datum_platnosti` FROM `zavody` WHERE `rok` = ? AND zobrazovat = 'ano' AND vysledky = 'ano'", $rok)->fetch();
		$datumPlatnosti = $result->datum;
		$datumPlatnostiOrig = $result->datum_platnosti;

		$result = $this->context->zavody->getZavody($rok);
		foreach ($result as $row) {
			$zavody[$row->id] = array('typ' => $row->typ, 'nazev' => $row->nazev, 'kategorie' => $row->kategorie);
		}

		$zavodnici = array();
		$query = "SELECT `z`.`cele_jmeno`, `z`.`registrace`, `zz`.`id_zavodnika`, `zz`.`id_zavodu`, `zav`.`kategorie` `kategorie_zavodu`, `zz`.`tym`, `zk`.`kategorie`, `cips1`, `umisteni1`, `cips2`, `umisteni2` FROM `zavodnici_zavody` `zz` JOIN `zavodnici` `z` ON `zz`.`id_zavodnika` = `z`.`id` JOIN `zavody` `zav` ON `zz`.`id_zavodu` = `zav`.`id` JOIN `zavodnici_kategorie` `zk` ON `zz`.`id_zavodnika` = `zk`.`id_zavodnika` WHERE (`cips1` IS NOT NULL OR `cips2` IS NOT NULL) AND (`zav`.`zobrazovat` = 'ano') AND (`zav`.`vysledky` = 'ano') ";
		if ($typ == 'u23')
			$query .= " AND `zk`.`kategorie` IN ('u23', 'u23_zena')";
		else if ($typ == 'u18')
			$query .= " AND `zk`.`kategorie` IN ('u18', 'u18_zena')";
		else if ($typ == 'u14')
			$query .= " AND `zk`.`kategorie` IN ('u14', 'u14_zena')";
		else if ($typ == 'zeny')
			$query .= " AND `zk`.`kategorie` IN ('u14_zena', 'u18_zena', 'u23_zena', 'zena')";

		$result = $this->context->database->query($query . " ORDER BY `zav`.`datum_od`");
		foreach ($result as $row) {
			$id = $row->id_zavodnika;
			if (!isset($zavodnici[$id]))
				$zavodnici[$id] = array('jmeno' => $row->cele_jmeno, 'min_body_zebricek' => 0, 'zavodu' => 0, 'registrace' => $row->registrace, 'tym' => $row->tym, 'kategorie' => Kategorie::$kategorie[$row->kategorie], 'body_celkem' => array(), 'cips_celkem' => array(), 'vysledky' => array($row->id_zavodu => array('zavod' => $row->id_zavodu, 'kategorie_zavodu' => $row->kategorie_zavodu, 'cips1' => $row->cips1, 'cips2' => $row->cips2, 'umisteni1' => $row->umisteni1, 'umisteni2' => $row->umisteni2, 'body' => 0)));
			else {
				$zavodnici[$id]['vysledky'][$row->id_zavodu] = array('zavod' => $row->id_zavodu, 'kategorie_zavodu' => $row->kategorie_zavodu, 'umisteni1' => $row->umisteni1, 'umisteni2' => $row->umisteni2, 'cips1' => $row->cips1, 'cips2' => $row->cips2, 'body' => 0);
			}
			if ($row->umisteni1 !== NULL)
				$zavodnici[$id]['zavodu']++;
			if ($row->umisteni2 !== NULL)
				$zavodnici[$id]['zavodu']++;
		}

		foreach ($zavodnici as $id => $z) {
			foreach ($z['vysledky'] as $k => $v) {
				$idZavodu = $v['zavod'];
				$typZavodu = $zavody[$idZavodu]['typ'];

				$body1 = $this->getBody($typZavodu, $v['umisteni1']);
				$body2 = $this->getBody($typZavodu, $v['umisteni2']);
				$cips1 = $v['cips1'];
				$cips2 = $v['cips2'];

				if ($v['umisteni1'] !== NULL) {
					$zavodnici[$id]['vysledky'][$k]['body1'] = $body1;
					$zavodnici[$id]['vysledky'][$k]['body1_zebricek'] = false;
					$zavodnici[$id]['body_celkem'][] = $body1;
					$zavodnici[$id]['cips_celkem'][] = $cips1;
				} else {
					$zavodnici[$id]['vysledky'][$k]['body1'] = NULL;
					$zavodnici[$id]['vysledky'][$k]['body1_zebricek'] = false;
				}
				if ($v['umisteni2'] !== NULL) {
					$zavodnici[$id]['vysledky'][$k]['body2'] = $body2;
					$zavodnici[$id]['vysledky'][$k]['body2_zebricek'] = false;
					$zavodnici[$id]['body_celkem'][] = $body2;
					$zavodnici[$id]['cips_celkem'][] = $cips2;
				} else {
					$zavodnici[$id]['vysledky'][$k]['body2'] = NULL;
					$zavodnici[$id]['vysledky'][$k]['body2_zebricek'] = false;
				}
			}
		}
		foreach ($zavodnici as $id => $z) {
			$zavodnici[$id]['body_zebricek'] = $zavodnici[$id]['body_celkem'];
			if (count($zavodnici[$id]['body_zebricek']) > 12) {
				rsort($zavodnici[$id]['body_zebricek']);
				$zavodnici[$id]['body_zebricek'] = array_slice($zavodnici[$id]['body_zebricek'], 0, 12);
			}
			if (count($zavodnici[$id]['body_zebricek']) > 11) {
				$temp = array_values($zavodnici[$id]['body_zebricek']);
				rsort($temp);
				$zavodnici[$id]['min_body_zebricek'] = array_pop($temp) + 1;
			} else {
				$zavodnici[$id]['min_body_zebricek'] = 0;
			}

			$bodyZebricekKopie = $zavodnici[$id]['body_zebricek'];
			foreach ($z['vysledky'] as $k => $v) {
				$body1 = $zavodnici[$id]['vysledky'][$k]['body1'];
				if ($body1 !== NULL) {
					$ind = array_search($body1, $bodyZebricekKopie);
					if ($ind !== false) {
						$zavodnici[$id]['vysledky'][$k]['body1_zebricek'] = true;
						unset($bodyZebricekKopie[$ind]);
					}
				}
				$body2 = $zavodnici[$id]['vysledky'][$k]['body2'];
				if ($body2 !== NULL) {
					$ind = array_search($body2, $bodyZebricekKopie);
					if ($ind !== false) {
						$zavodnici[$id]['vysledky'][$k]['body2_zebricek'] = true;
						unset($bodyZebricekKopie[$ind]);
					}
				}
			}
		}

		uasort($zavodnici, array($this, 'bodySort'));
		return array('zavody' => $zavody, 'zavodnici' => $zavodnici, 'datum_platnosti' => $datumPlatnosti, 'datum_platnosti_orig' => $datumPlatnostiOrig);
	}

	private function getBody($typZavodu, $umisteni) {
		$bodovaciTabulka = self::$bodovaci_tabulky[self::$bodovani_zavodu[$typZavodu]];
		if ($umisteni === NULL)
			return NULL;
		$umisteni = (int) $umisteni;
		if (isset($bodovaciTabulka[$umisteni]))
			$body = $bodovaciTabulka[$umisteni];
		else
			$body = 0;
		return $body;
	}

	public function getVysledkyZavodu($idZavodnika, $rok) {

		$zavodnik = array();
		$headerSet = false;
		$result = $this->context->database->query("SELECT `zav`.`id` `id_zavodu`, `zav`.`nazev` `nazev_zavodu`, `zav`.`typ` `typ`, `zav`.`kategorie` `kategorie_zavodu`, `zz`.`tym`, `zk`.`kategorie`, `cips1`, `umisteni1`, `cips2`, `umisteni2` FROM `zavodnici_zavody` `zz` JOIN `zavodnici` `z` ON `zz`.`id_zavodnika` = `z`.`id` JOIN `zavody` `zav` ON `zz`.`id_zavodu` = `zav`.`id` JOIN `zavodnici_kategorie` `zk` ON `zz`.`id_zavodnika` = `zk`.`id_zavodnika` WHERE (`cips1` IS NOT NULL OR `cips2` IS NOT NULL) AND (`zav`.`zobrazovat` = 'ano') AND (`zav`.`vysledky` = 'ano') AND `z`.`id` = ? ORDER BY `zav`.`datum_od`", $idZavodnika);
		foreach ($result as $row) {
			if (!$headerSet) {
				$zavodnik = array('zavodu' => 0, 'body_celkem' => array(), 'vysledky' => array($row->id_zavodu => array('nazev_zavodu' => $row->nazev_zavodu, 'tym' => $row['tym'], 'typ_zavodu' => $row->typ, 'kategorie_zavodu' => $row->kategorie_zavodu, 'id_zavodu' => $row->id_zavodu, 'umisteni1' => $row->umisteni1, 'umisteni2' => $row->umisteni2, 'cips1' => $row->cips1, 'cips2' => $row->cips2)));
				$headerSet = true;
			} else {
				$zavodnik['vysledky'][$row->id_zavodu] = array('nazev_zavodu' => $row->nazev_zavodu, 'tym' => $row['tym'], 'typ_zavodu' => $row->typ, 'kategorie_zavodu' => $row->kategorie_zavodu, 'id_zavodu' => $row->id_zavodu, 'umisteni1' => $row->umisteni1, 'umisteni2' => $row->umisteni2, 'cips1' => $row->cips1, 'cips2' => $row->cips2);
			}
			if ($row->umisteni1 !== NULL)
				$zavodnik['zavodu']++;
			if ($row->umisteni2 !== NULL)
				$zavodnik['zavodu']++;
		}

		if (isset($zavodnik['vysledky'])) {
			foreach ($zavodnik['vysledky'] as $k => $v) {
				$typZavodu = $v['typ_zavodu'];
	
				$body1 = $this->getBody($typZavodu, $v['umisteni1']);
				$body2 = $this->getBody($typZavodu, $v['umisteni2']);
	
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
		$zavodnik = $this->context->zavodnici->getZavodnikById($idZavodnika, $rok);
		$vysledky = $this->getVysledkyZavodu($idZavodnika, $rok);
		return array('zavodnik' => $zavodnik, 'vysledky' => (isset($vysledky['vysledky']) ? $vysledky['vysledky'] : NULL));
	}

	private function bodySort($a, $b) {
		$sumA = array_sum($a['body_zebricek']);
		$sumB = array_sum($b['body_zebricek']);
		$sumCips1 = array_sum($a['cips_celkem']);
		$sumCips2 = array_sum($b['cips_celkem']);
		if ($sumA == $sumB) {
			if ($sumCips1 > $sumCips2) {
				return -1;
			} else if ($sumCips1 < $sumCips2) {
				return 1;
			} else {
				return 0;
			}
		} else if ($sumA > $sumB) {
			return -1;
		} else {
			return 1;
		}
	}

}