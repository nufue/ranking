<?php
	Header("Content-type: text/html; charset=utf-8");
	mb_internal_encoding('utf-8');
	
	$bodovaci_tabulky = array(
		1 => array(1 => 40, 38, 36, 34, 32, 30, 28, 26, 24, 22, 20, 18, 16, 14, 12, 10, 8, 6, 4, 2, 1),
		2 => array(1 => 36, 33, 31, 29, 27, 25, 22, 19, 16, 13, 10, 7, 4, 1),
		3 => array(1 => 30, 27, 25, 23, 21, 19, 16, 13, 9, 6, 3, 1),
		4 => array(1 => 25, 22, 19, 16, 13, 11, 10, 8, 5, 3, 2, 1),
		5 => array(1 => 15, 12, 10, 8, 6, 4, 2, 1),
	);
	
	$bodovani_zavodu = array(
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
	
	
	mysql_connect("vps.nufu.eu", "plavana", 'G44^=89<K+r4E]P');
	mysql_select_db("plavana");
	mysql_query("SET NAMES 'utf8'");


	$zavody = array();

	$result = mysql_query("SELECT `id`, `typ`, `nazev` FROM `zavody` WHERE `rok` = 2012");
	while ($row = mysql_fetch_array($result)) {
		$zavody[$row['id']] = array('typ' => $row['typ'], 'nazev' => $row['nazev']);
	}
	
	$zavodnici = array();	
	$result = mysql_query($q="SELECT `z`.`cele_jmeno`, `zz`.`id_zavodnika`, `zz`.`id_zavodu`, `cips1`, `umisteni1`, `cips2`, `umisteni2` FROM `zavodnici_zavody` `zz` JOIN `zavodnici` `z` ON `zz`.`id_zavodnika` = `z`.`id` WHERE (`cips1` IS NOT NULL OR `cips2` IS NOT NULL)");
	while ($row = mysql_fetch_array($result)) {
		$id = $row['id_zavodnika'];
		if (!isset($zavodnici[$id])) $zavodnici[$id] = array('jmeno' => $row['cele_jmeno'], 'body_celkem' => array(), 'vysledky' => array($row['id_zavodu'] => array('zavod' => $row['id_zavodu'], 'umisteni1' => $row['umisteni1'], 'umisteni2' => $row['umisteni2'], 'body' => 0)));
		else {
			$zavodnici[$id]['vysledky'][$row['id_zavodu']] = array('zavod' => $row['id_zavodu'], 'umisteni1' => $row['umisteni1'], 'umisteni2' => $row['umisteni2'], 'body' => 0); 
		}
	}
	
	foreach ($zavodnici as $id => $z) {
		foreach ($z['vysledky'] as $k => $v) {
			$idZavodu = $v['zavod'];
			$typZavodu = $zavody[$idZavodu]['typ'];
			$umisteni1 = (int)$v['umisteni1'];
			$umisteni2 = (int)$v['umisteni2'];
			$bodovaciTabulka = $bodovaci_tabulky[$bodovani_zavodu[$typZavodu]];
			
			if ($umisteni1 > (int)$umisteni1) {
				$umisteni1 = (int)$umisteni1 + 1;
			}
			if ($umisteni2 > (int)$umisteni2) {
				$umisteni2 = (int)$umisteni2 + 1;
			}
			$body1 = $body2 = 0;  
			if (isset($bodovaciTabulka[$umisteni1])) {
				$body1 = $bodovaciTabulka[$umisteni1];
			}
			if (isset($bodovaciTabulka[$umisteni2])) {
				$body2 = $bodovaciTabulka[$umisteni2];
			}
			$zavodnici[$id]['vysledky'][$k]['body1'] = $body1;
			$zavodnici[$id]['vysledky'][$k]['body2'] = $body2;
			$zavodnici[$id]['body_celkem'][] = $body1;
			$zavodnici[$id]['body_celkem'][] = $body2;
		}
	}
	foreach ($zavodnici as $id => $z) {
		if (count($zavodnici[$id]['body_celkem']) > 12) {
			rsort($zavodnici[$id]['body_celkem']);
			$zavodnici[$id]['body_celkem'] = array_slice($zavodnici[$id]['body_celkem'], 0, 12);
		}
	}
	
	uasort($zavodnici, 'bodySort');
	echo "<table border=1>";
	echo "<tr><th>Pořadí</th><th>Jméno</th><th>Celkem bodů</th>";
	foreach ($zavody as $z) {
		echo "<th>$z[nazev]</th>";
	}
	echo "</tr>";
	$poradi = 1;
	foreach ($zavodnici as $id => $z) {
		echo "<tr><td>$poradi.</td><td>".$z['jmeno']."</td><td>".array_sum($z['body_celkem'])."</td>";
		foreach ($zavody as $idZavodu => $v) {
			echo "<td>";
			if (isset($z['vysledky'][$idZavodu])) {
				echo $z['vysledky'][$idZavodu]['body1'], '+', $z['vysledky'][$idZavodu]['body2'];
			} else echo "-";
			echo "</td>";
		}
		echo "</tr>";
		$poradi++;
	}
	echo "</table>";
	
	function bodySort($a, $b) {
		$sumA = array_sum($a['body_celkem']);
		$sumB = array_sum($b['body_celkem']);
		if ($sumA == $sumB) return 0;
		else if ($sumA > $sumB) return -1;
		else return 1;		
	}
	
	//echo "<pre>";
	//var_dump($zavodnici);
	//echo "</pre>";
