<?php

class Tymy extends Base {

	public function getTymy() {
		$query = "SELECT `zz`.`id_zavodnika`, `zz`.`id_zavodu`, `z`.`cele_jmeno`, `zz`.`tym` FROM `zavodnici` `z` JOIN `zavodnici_zavody` `zz` ON `z`.`id` = `zz`.`id_zavodnika` WHERE `id_zavodnika` IN (SELECT DISTINCT `id_zavodnika` FROM `zavodnici_zavody` GROUP BY `id_zavodnika` HAVING COUNT(`id_zavodnika`) > 1) ORDER BY `id_zavodnika`";
		$dbResult = $this->database->query($query)->fetchAll();
		$result = array();
		foreach ($dbResult as $row) {
			if (!isset($result[$row->id_zavodnika])) {
				$result[$row->id_zavodnika] = array('jmeno' => $row->cele_jmeno, 'zavody' => array(array('id' => $row->id_zavodu, 'tym' => $row->tym)));
			} else {
				$found = false;
				foreach ($result[$row->id_zavodnika]['zavody'] as $v) {
					if ($v['tym'] == $row->tym) {
						$found = true;
						break;
					}
				}
				if (!$found) {
					$result[$row->id_zavodnika]['zavody'][] = array('id' => $row->id_zavodu, 'tym' => $row->tym);
				}
			}
		}
		foreach ($result as $k => $v) {
			if (count($v['zavody']) == 1)
				unset($result[$k]);
		}
		return $result;
	}
	
	public function getTymyRok($rok) {
		return $this->database->query("SELECT t.*, count(tz.id_zavodnika) `pocet_zavodniku` FROM tymy t LEFT JOIN tymy_zavodnici tz ON t.id = tz.id_tymu WHERE rok = ? GROUP BY `id` ORDER BY `liga`, `kod`", $rok);
	}
	
	public function getTym($id) {
		$info = $this->database->query("SELECT * FROM tymy WHERE id = ?", $id)->fetch();
		$zavodnici = $this->database->query("SELECT z.id, z.cele_jmeno, z.registrace FROM zavodnici z JOIN zavodnici_kategorie zk ON z.id = zk.id_zavodnika JOIN tymy_zavodnici tz ON tz.id_zavodnika = z.id WHERE tz.id_tymu = ? AND zk.rok = ?", $id, $info->rok)->fetchAll();
		return array('info' => $info, 'zavodnici' => $zavodnici);
	}

}