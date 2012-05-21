<?php
	Header("Content-type: text/html; charset=utf-8");
	mb_internal_encoding('utf-8');
	
	function showHeader() {
	?><!doctype html>
	<html>
	<head>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
 	</head>
	<body><?php
	}
	
	$typyZavodu = array(
            'memicr' => 'MeMi ČR seniorů',
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
	
	$bodovaci_tabulky = array(
		1 => array(40, 38, 36, 34, 32, 30, 28, 26, 24, 22, 20, 18, 16, 14, 12, 10, 8, 6, 4, 2, 1),
		2 => array(36, 33, 31, 29, 27, 25, 22, 19, 16, 13, 10, 7, 4, 1),
		3 => array(30, 27, 25, 23, 21, 19, 16, 13, 9, 6, 3, 1),
		4 => array(25, 22, 19, 16, 13, 11, 10, 8, 5, 3, 2, 1),
		5 => array(15, 12, 10, 8, 6, 4, 2, 1),
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
	
	
	if (!isset($_GET['akce'])) {
	
	mysql_connect("localhost", "root", '***REMOVED***');
	mysql_select_db("plavana");
	mysql_query("SET NAMES 'utf8'");
	$result = mysql_query("SELECT * FROM zavody WHERE vysledky = 'ne'");
	
	showHeader();
	
?>
<form action="?akce=analyza" method="post">
<?php
	while ($row = mysql_fetch_array($result)) {
		echo "<input type='radio' name='zavod' id='zavod$row[id]' value='$row[id]'> $row[nazev]";
		if (!empty($row['kategorie'])) echo " - ".$row['kategorie'];
		echo "<br>";
	}
	echo "<input type='radio' name='zavod' id='zavod_jiny' value='jiny'> jiný<br>";
	echo "<table id='jinyZavod'>";
	echo "<tr><th><label for='typ'>Typ závodu</label></th><td>";
	echo "<select id='typ' name='typ'>";
	foreach ($typyZavodu as $k => $v) echo "<option value='$k'>$v</option>";
	echo "</select>";
	echo "</td></tr>";
	echo "<tr><th><label for='nazev'>Název závodu</label></th><td>";
	echo "<input type='text' name='nazev' id='nazev' size='60'>";
	echo "</td></tr>";
	echo "<tr><th><label for='datum_od'>Datum od</label></th><td>";
	echo "<input type='text' name='datum_od' id='datum_od' size='15'>";
	echo "</td></tr>";
	echo "<tr><th><label for='datum_do'>Datum do</label></th><td>";
	echo "<input type='text' name='datum_do' id='datum_do' size='15'>";
	echo "</td></tr>";
	echo "</table>";
?>
<textarea name='data' cols=80 rows=5></textarea>
<br><input type=submit value='Analýza výsledků'>
</form>
<script type='text/javascript'>
	$(document).ready(function() {
		$('#jinyZavod').hide();
		$('#zavod_jiny').click(function() {
			$('#jinyZavod').show();
		});
	});
</script>
<?php
	}

$sloupce = array(
	'registrace' => 'Registrace',
	'prijmeni' => 'Příjmení, jméno',
	'kategorie' => 'Kategorie',
	'druzstvo' => 'Družstvo',
	'cips1' => '1. závod CIPS',
	'umisteni1' => '1. závod umístění',
	'cips2' => '2. závod CIPS',
	'umisteni2' => '2. závod umístění',
	'pocet_zavodu' => 'Počet závodů',
	'cips_celkem' => 'CIPS celkem',
	'body_celkem' => 'Součet umístění',
	'umisteni_celkem' => 'Celkové umístění',
);

if (isset($_POST['data']) && isset($_GET['akce']) && $_GET['akce'] == 'analyza') {
	$lines = explode("\n", $_POST['data']);
	
	echo "<form action='?akce=ulozit' method='post'>";
	
	echo "<p><input type='submit' value='Uložit výsledky'></p>";
	echo "<table border=1>";
	echo "<tbody>";
	
	$pocetSloupcu = NULL;
	
	$tabulka = array();
	$radky = 0;
	
	foreach ($lines as $line) {
		echo "<tr>";
		$cols = explode("\t", $line);
		$sloupcu = 0;
		$radek = array();
		echo "<td><input type='radio' name='row' id='row$radky' value='$radky'></td>";
		$radky++;
		foreach ($cols as $col) {
			$col = trim($col);
			$radek[] = $col;
			echo "<td>";
			//echo "<input type='text' name='table[$radky][$sloupcu]' value='$col' size='".(mb_strlen($col) > 0 ? mb_strlen($col) : 1)."'></td>";
			echo $col;
			
			$sloupcu++;
		}
		$tabulka[] = $radek;
		if ($pocetSloupcu == NULL) $pocetSloupcu = $sloupcu;
		if ($sloupcu > $pocetSloupcu) $pocetSloupcu = $sloupcu;
		echo "</tr>";
	}
	echo "<thead>";
	
	echo "<tr><th>První řádek</th>";
	$cipsFound = 0;
	$umisteniFound = 0;
	for ($i = 0; $i < $pocetSloupcu; $i++) {
		$pravdepodobnyTyp = '';
		
		
		foreach ($tabulka as $radek) {
			if (isset($radek[$i])) {
				if (mb_strtolower($radek[$i]) == 'reg') { $pravdepodobnyTyp = 'registrace'; break; }
				if ($radek[$i] == 'Příjmení, jméno' || $radek[$i] == 'Příjmení jméno') { $pravdepodobnyTyp = 'prijmeni'; break; }
				if (mb_strtolower($radek[$i]) == 'kat') { $pravdepodobnyTyp = 'kategorie'; break; }
				if ($radek[$i] == 'Družstvo' || $radek[$i] == 'Organizace') { $pravdepodobnyTyp = 'druzstvo'; break; }
				if ($radek[$i] == 'CIPS') {
				    if ($cipsFound == 0) { $pravdepodobnyTyp = 'cips1'; $cipsFound++; break; }
				    if ($cipsFound == 1) { $pravdepodobnyTyp = 'cips2'; $cipsFound++; break; }
				    if ($cipsFound == 2) { $pravdepodobnyTyp = 'cips_celkem'; $cipsFound++; break; }
				}
				if ($radek[$i] == 'um.' || $radek[$i] == 'Poř.') {
					if ($umisteniFound == 0) { $pravdepodobnyTyp = 'umisteni1'; $umisteniFound++; break; }
					if ($umisteniFound == 1) { $pravdepodobnyTyp = 'umisteni2'; $umisteniFound++; break; }
					if ($umisteniFound == 2) { $pravdepodobnyTyp = 'umisteni_celkem'; $umisteniFound++; break; }
				}
				if (mb_strtolower($radek[$i]) == 'body') { $pravdepodobnyTyp = 'body_celkem'; break; }
			}
		}
		
		
		echo "<th>";
		echo "<select id='sloupec$i' name='sloupec$i'>\n";
		echo "<option value=''>-</option>\n";
		foreach ($sloupce as $k => $v) {
			echo "<option value='$k'";
			if ($k == $pravdepodobnyTyp) echo " selected='selected'";
			echo ">$v</option>\n";
		}
		echo "</select></th>\n";
	}
	echo "</tr>";
	echo "</thead>";
	echo "</tbody>";
	echo "</table>";
	echo "<input type='hidden' name='tabulka' id='tabulka' value='".base64_encode(serialize($tabulka))."'>";
	echo "<input type='submit' value='Uložit výsledky'>"; 
	echo "</form>";
//	echo "<pre>";
//	var_dump($tabulka);
//	echo "</pre>";
	
} else if (isset($_GET['akce']) && $_GET['akce'] == 'ulozit') {
	
	$tabulka = unserialize(base64_decode($_POST['tabulka']));
	$sloupce = array();
	foreach ($_POST as $k => $v) {
		if (mb_substr($k, 0, 7) == 'sloupec') {
			$cisloSloupce = (int)str_replace('sloupec', '', $k);
			if (!empty($v)) $sloupce[$v] = $cisloSloupce;
		}
	}
	
	$typZavodu = $_POST['typ'];
	$nazevZavodu = $_POST['nazev'];
	$datumOd = $_POST['datum_od'];
	$datumDo = $_POST['datum_do'];
	
	$e = explode(".", $datumOd);
	$datumOd = Date("Y-m-d", strtotime($e[2]."-".$e[1]."-".$e[0]));
	
	$e = explode(".", $datumDo);
	$datumDo = Date("Y-m-d", strtotime($e[2]."-".$e[1]."-".$e[0]));
	
	//mysql_connect("vps.nufu.eu", "plavana", 'G44^=89<K+r4E]P');
	mysql_connect("localhost", "root", '***REMOVED***');
	mysql_select_db("plavana");
	mysql_query("SET NAMES 'utf8'");
	
	
	echo "<table>";
	echo "<tr><th>Typ závodu</th><td>".$typyZavodu[$typZavodu]."</td></tr>";
	echo "<tr><th>Název závodu</th><td>".$nazevZavodu."</td></tr>";
	echo "<tr><th>Datum konání</th><td>$datumOd - $datumDo</td></tr>";
	echo "</table>";
	
	mysql_query($q="INSERT INTO zavody(id, rok, nazev, typ, datum_od, datum_do) values (NULL, 2012, '$nazevZavodu', '$typZavodu', '$datumOd', '$datumDo')");
	echo $q."<br>";
	$idZavodu = mysql_insert_id();
	
	
	
	echo "<table>";
	echo "<tr><th rowspan='2'>Číslo registrace</th><th rowspan='2'>Příjmení, jméno</th><th colspan='2'>1. závod</th><th colspan='2'>2. závod</th><th>Poznámka</th></tr>";
	echo "<tr><th>CIPS</th><th>Umístění</th><th>CIPS</th><th>Umístění</th></tr>\n";
	$row = $_POST['row'];
	$radku = 0;
	foreach ($tabulka as $radek) {
		if ($radku++ < $row) continue;
		$prijmeni = trim($radek[$sloupce['prijmeni']]);
		$registrace = trim($radek[$sloupce['registrace']]);
		if ($prijmeni == '') continue;
		
		
		
		$poznamka = '';
		$idZavodnika = '';
		if (!preg_match('~\d+~', $registrace)) $poznamka = 'nepůjde do žebříčku';
		else {
			$result = mysql_query($q="SELECT id, cele_jmeno FROM zavodnici WHERE registrace = '$registrace'");
			echo $q."<br>";
			if ($result && mysql_num_rows($result) > 0) {
				$idZavodnika = mysql_result($result, 0, 0);
			} else {
				mysql_query($q="INSERT INTO zavodnici(id, registrace, cele_jmeno) VALUES (NULL, '$registrace', '$prijmeni')");
				echo $q."<br>\n";
				$idZavodnika = mysql_insert_id(); 
			} 
		}
		
		$tym = $radek[$sloupce['druzstvo']];
		
		$cips1 = trim($radek[$sloupce['cips1']]);
		$cips2 = trim($radek[$sloupce['cips2']]);
		$umisteni1 = trim($radek[$sloupce['umisteni1']]);
		$umisteni2 = trim($radek[$sloupce['umisteni2']]);
		$kategorie = trim($radek[$sloupce['kategorie']]);
		
		if ($cips1 === '') $cips1 = 'NULL';
		if ($cips2 === '') $cips2 = 'NULL';
		if ($umisteni1 === '') $umisteni1 = 'NULL';
		if ($umisteni2 === '') $umisteni2 = 'NULL';
		
		$umisteni1 = str_replace(',', '.', $umisteni1);
		$umisteni2 = str_replace(',', '.', $umisteni2);
		
		echo "<tr>";
		echo "<td>", $registrace, "</td>";
		echo "<td>", $prijmeni, "</td>";
		echo "<td>", $tym, "</td>";
		echo "<td>", $cips1, "</td>";
		echo "<td>", $umisteni1, "</td>";
		echo "<td>", $cips2, "</td>";
		echo "<td>", $umisteni2, "</td>";		
		echo "<td>", $poznamka, "</td>";
		echo "</tr>\n";
		if (!empty($idZavodnika)) {
			mysql_query($q="INSERT INTO zavodnici_zavody(id_zavodu, id_zavodnika, tym, kategorie, cips1, umisteni1, cips2, umisteni2) VALUES ($idZavodu, $idZavodnika, '$tym', '$kategorie', $cips1, $umisteni1, $cips2, $umisteni2)");
			echo $q."<br>".mysql_error()."<br>\n";
		}
	}
	echo "</table>";
	

	echo "<pre>";
	var_dump($_POST['row']);
	var_dump($sloupce);
	var_dump($tabulka);
	
	echo "</pre>";

}

