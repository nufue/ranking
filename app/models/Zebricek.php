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

	public function getZebricek($rok) {
		$zavody = array();

		$result = $this->context->database->query("SELECT DATE_FORMAT(MAX(`datum_do`), '%e. %c. %Y') `datum` FROM `zavody` WHERE `rok` = ? AND zobrazovat = 'ano' AND vysledky = 'ano'", $rok)->fetch();
		$datumPlatnosti = $result->datum;

		$result = $this->context->zavody->getZavody($rok);
		foreach ($result as $row) {
			$zavody[$row->id] = array('typ' => $row->typ, 'nazev' => $row->nazev);
		}

		$zavodnici = array();
		$result = $this->context->database->query("SELECT `z`.`cele_jmeno`, `z`.`registrace`, `zz`.`id_zavodnika`, `zz`.`id_zavodu`, `zz`.`tym`, `zk`.`kategorie`, `cips1`, `umisteni1`, `cips2`, `umisteni2` FROM `zavodnici_zavody` `zz` JOIN `zavodnici` `z` ON `zz`.`id_zavodnika` = `z`.`id` JOIN `zavody` `zav` ON `zz`.`id_zavodu` = `zav`.`id` JOIN `zavodnici_kategorie` `zk` ON `zz`.`id_zavodnika` = `zk`.`id_zavodnika` WHERE (`cips1` IS NOT NULL OR `cips2` IS NOT NULL) AND (`zav`.`zobrazovat` = 'ano') AND (`zav`.`vysledky` = 'ano') ORDER BY `zav`.`datum_od`");
		foreach ($result as $row) {
			$id = $row->id_zavodnika;
			if (!isset($zavodnici[$id]))
				$zavodnici[$id] = array('jmeno' => $row->cele_jmeno, 'zavodu' => 0, 'registrace' => $row->registrace, 'tym' => $row->tym, 'kategorie' => Kategorie::$kategorie[$row->kategorie], 'body_celkem' => array(), 'vysledky' => array($row->id_zavodu => array('zavod' => $row->id_zavodu, 'umisteni1' => $row->umisteni1, 'umisteni2' => $row->umisteni2, 'body' => 0)));
			else {
				$zavodnici[$id]['vysledky'][$row->id_zavodu] = array('zavod' => $row->id_zavodu, 'umisteni1' => $row->umisteni1, 'umisteni2' => $row->umisteni2, 'body' => 0);
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

				$umisteni1 = (int) $v['umisteni1'];
				$umisteni2 = (int) $v['umisteni2'];
				$bodovaciTabulka = self::$bodovaci_tabulky[self::$bodovani_zavodu[$typZavodu]];

				$body1 = $body2 = 0;
				if (isset($bodovaciTabulka[$umisteni1])) {
					$body1 = $bodovaciTabulka[$umisteni1];
				}
				if (isset($bodovaciTabulka[$umisteni2])) {
					$body2 = $bodovaciTabulka[$umisteni2];
				}
				if ($v['umisteni1'] !== NULL) {
					$zavodnici[$id]['vysledky'][$k]['body1'] = $body1;
					$zavodnici[$id]['body_celkem'][] = $body1;
				} else {
					$zavodnici[$id]['vysledky'][$k]['body1'] = NULL;
				}
				if ($v['umisteni2'] !== NULL) {
					$zavodnici[$id]['vysledky'][$k]['body2'] = $body2;
					$zavodnici[$id]['body_celkem'][] = $body2;
				} else {
					$zavodnici[$id]['vysledky'][$k]['body2'] = NULL;
				}
			}
		}
		foreach ($zavodnici as $id => $z) {
			$zavodnici[$id]['body_zebricek'] = $zavodnici[$id]['body_celkem'];
			if (count($zavodnici[$id]['body_zebricek']) > 12) {
				rsort($zavodnici[$id]['body_zebricek']);
				$zavodnici[$id]['body_zebricek'] = array_slice($zavodnici[$id]['body_zebricek'], 0, 12);
			}
		}

		uasort($zavodnici, array($this, 'bodySort'));
		return array('zavody' => $zavody, 'zavodnici' => $zavodnici, 'datum_platnosti' => $datumPlatnosti);
	}

	private function bodySort($a, $b) {
		$sumA = array_sum($a['body_zebricek']);
		$sumB = array_sum($b['body_zebricek']);
		if ($sumA == $sumB)
			return 0;
		else if ($sumA > $sumB)
			return -1;
		else
			return 1;
	}

}