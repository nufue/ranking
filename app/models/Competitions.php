<?php

namespace App\Model;

final class Competitions extends Base
{

	public function loadAllCompetitions($year)
	{
		return $this->database->query("SELECT * FROM zavody WHERE `rok` = ? ORDER BY `datum_od`, `nazev`", $year)->fetchAll();
	}

	public function loadVisibleCompetitions($year)
	{
		return $this->database->query("SELECT * FROM zavody WHERE `rok` = ? AND `zobrazovat` = 'ano' AND `vysledky` = 'ano' ORDER BY `datum_od`, `nazev`", $year)->fetchAll();
	}

	public function getCompetition($id)
	{
		return $this->database->query("SELECT `id`, `nazev`, `typ`, `datum_od`, `datum_do`, `zobrazovat`, `vysledky`, `kategorie`, `rok` FROM `zavody` WHERE `id` = ?", $id)->fetch();
	}

	public function updateCompetition($id, $values)
	{
		$this->database->query("UPDATE zavody SET `nazev` = ?, `typ` = ?, `datum_od` = ?, `datum_do` = ?, `zobrazovat` = ?, `vysledky` = ? WHERE `id`= ? ", $values['nazev'], $values['typ'], $values['datum_od'], $values['datum_do'], $values['zobrazovat'], $values['vysledky'], $id);
	}

	public function addCompetition($values)
	{
		$rok = $values['datum_od']->format('Y');
		$this->database->query("INSERT INTO zavody(nazev, kategorie, typ, rok, datum_od, datum_do, zobrazovat, vysledky) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", $values['nazev'], $values['kategorie'], $values['typ'], $rok, $values['datum_od'], $values['datum_do'], $values['zobrazovat'], $values['vysledky']);
	}

	public function addVysledek($idZavodu, $idZavodnika, $tym, $cips1, $umisteni1, $cips2, $umisteni2): void
	{
		$this->database->table('zavodnici_zavody')->insert([
			'id_zavodu' => $idZavodu,
			'id_zavodnika' => $idZavodnika,
			'tym' => $tym,
			'cips1' => $cips1,
			'umisteni1' => $umisteni1,
			'cips2' => $cips2,
			'umisteni2' => $umisteni2,
		]);
	}

	public function deleteResults(int $competitionId): void
	{
		$this->database->query('DELETE FROM zavodnici_zavody WHERE id_zavodu = ?', $competitionId);
		$this->database->query("UPDATE `zavody` SET `vysledky` = 'ne' WHERE `id` = ?", $competitionId);
	}

	public function markResultsPresent(int $competitionId): void
	{
		$this->database->query("UPDATE `zavody` SET `vysledky` = 'ano' WHERE `id` = ?", $competitionId);
	}

	/**
	 * @return Competition[]
	 */
	public function loadWithMissingResults(): array
	{
		$result = [];
		$competitions = $this->database->query('SELECT * FROM `zavody` WHERE `zobrazovat` = ? AND `vysledky` = ? AND `datum_do` < DATE_SUB(CURDATE(), INTERVAL 1 DAY) ORDER BY `datum_od`', 'ano', 'ne')->fetchAll();
		foreach ($competitions as $c) {
			$result[] = Competition::fromRow($c);
		}
		return $result;
	}

	public function getCompetitionYear(int $competitionId): int
	{
		return (int)$this->database->query("SELECT `rok` FROM `zavody` WHERE `id` = ?", $competitionId)->fetchField();
	}

}