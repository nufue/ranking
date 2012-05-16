<?php

class Kategorie extends Base {

    public function getKategorie() {
	$query = "SELECT `zz`.`id_zavodnika`, `zz`.`id_zavodu`, `z`.`cele_jmeno`, `zz`.`kategorie` FROM `zavodnici` `z` JOIN `zavodnici_zavody` `zz` ON `z`.`id` = `zz`.`id_zavodnika` WHERE `id_zavodnika` IN (SELECT DISTINCT `id_zavodnika` FROM `zavodnici_zavody` GROUP BY `id_zavodnika` HAVING COUNT(`id_zavodnika`) > 1) ORDER BY `id_zavodnika`";
	$dbResult = $this->database->query($query)->fetchAll();
	$result = array();
	foreach ($dbResult as $row) {
	    if (!isset($result[$row->id_zavodnika])) {
		$result[$row->id_zavodnika] = array('jmeno' => $row->cele_jmeno, 'zavody' => array(array('id' => $row->id_zavodu, 'kategorie' => $row->kategorie)));
	    } else {
		$found = false;
		foreach ($result[$row->id_zavodnika]['zavody'] as $v) {
		    if ($v['kategorie'] == $row->kategorie) {
			$found = true;
			break;
		    }
		}
		if (!$found) {
		    $result[$row->id_zavodnika]['zavody'][] = array('id' => $row->id_zavodu, 'kategorie' => $row->kategorie);
		}
	    }
	}
	foreach ($result as $k => $v) {
	    if (count($v['zavody']) == 1) unset($result[$k]);
	}
	return $result;
    }

}