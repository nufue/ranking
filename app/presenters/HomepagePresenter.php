<?php

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class HomepagePresenter extends BasePresenter {

	public function renderDefault($show = false) {
		$this->template->zobrazitZavody = $show;



		$zavody = array();

		$result = $this->context->database->query("SELECT DATE_FORMAT(MAX(`datum_do`), '%e. %c. %Y') `datum` FROM `zavody` WHERE `rok` = ?", 2012)->fetch();
		$this->template->datum_platnosti = $result->datum;

		$result = $this->context->database->query("SELECT `id`, `typ`, `nazev` FROM `zavody` WHERE `rok` = ? ORDER BY `datum_od`", 2012);
		foreach ($result as $row) {
			$zavody[$row->id] = array('typ' => $row->typ, 'nazev' => $row->nazev);
		}

		$zavodnici = array();
		$result = $this->context->database->query("SELECT `z`.`cele_jmeno`, `z`.`registrace`, `zz`.`id_zavodnika`, `zz`.`id_zavodu`, `zz`.`tym`, `zz`.`kategorie`, `cips1`, `umisteni1`, `cips2`, `umisteni2` FROM `zavodnici_zavody` `zz` JOIN `zavodnici` `z` ON `zz`.`id_zavodnika` = `z`.`id` JOIN `zavody` `zav` ON `zz`.`id_zavodu` = `zav`.`id` WHERE (`cips1` IS NOT NULL OR `cips2` IS NOT NULL) ORDER BY `zav`.`datum_od`");
		foreach ($result as $row) {
			$id = $row->id_zavodnika;
			if (!isset($zavodnici[$id]))
				$zavodnici[$id] = array('jmeno' => $row->cele_jmeno, 'zavodu' => 0, 'registrace' => $row->registrace, 'tym' => $row->tym, 'kategorie' => $row->kategorie, 'body_celkem' => array(), 'vysledky' => array($row->id_zavodu => array('zavod' => $row->id_zavodu, 'umisteni1' => $row->umisteni1, 'umisteni2' => $row->umisteni2, 'body' => 0)));
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
				$bodovaciTabulka = Zavody::$bodovaci_tabulky[Zavody::$bodovani_zavodu[$typZavodu]];

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
		$this->template->zavody = $zavody;
		$this->template->zavodnici = $zavodnici;
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
