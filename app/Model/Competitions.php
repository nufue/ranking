<?php
declare(strict_types=1);

namespace App\Model;

use App\Exceptions\CompetitionNotFoundException;

final class Competitions extends Base
{

	/**
	 * @return Competition[]
	 */
	public function loadAllCompetitions(int $year): array
	{
		$result = [];
		$competitions = $this->database->query("SELECT `z`.*, `cc`.`id` `categoryId`, `cc`.`output_description` `categoryDescription` FROM `zavody` `z` JOIN `competition_categories` `cc` ON `z`.`kategorie` = `cc`.`id` WHERE `z`.`rok` = ? ORDER BY `z`.`datum_od`, `z`.`nazev`", $year)->fetchAll();
		foreach ($competitions as $c) {
			$result[] = Competition::fromRow($c);
		}
		return $result;
	}

	/**
	 * @return Competition[]
	 */
	public function loadVisibleCompetitions(int $year): array
	{
		$result = [];
		$competitions = $this->database->query("SELECT `z`.*, `cc`.`id` `categoryId`, `cc`.`output_description` `categoryDescription` FROM `zavody` `z` JOIN `competition_categories` `cc` ON `z`.`kategorie` = `cc`.`id` WHERE `z`.`rok` = ? AND `z`.`zobrazovat` = 'ano' AND `z`.`vysledky` = 'ano' ORDER BY `z`.`datum_od`, `z`.`nazev`", $year)->fetchAll();
		foreach ($competitions as $c) {
			$result[] = Competition::fromRow($c);
		}
		return $result;
	}

	public function getCompetition(int $id): Competition
	{
		$competition = $this->database->query("SELECT `z`.`id`, `z`.`nazev`, `z`.`typ`, `z`.`datum_od`, `z`.`datum_do`, `z`.`zobrazovat`, `z`.`vysledky`, `z`.`rok`, `cc`.`id` `categoryId`, `cc`.`output_description` `categoryDescription` FROM `zavody` `z` JOIN `competition_categories` `cc` ON `z`.`kategorie` = `cc`.`id` WHERE `z`.`id` = ?", $id)->fetch();
		if ($competition !== false) {
			return Competition::fromRow($competition);
		} else {
			throw new CompetitionNotFoundException('Závod s ID = '.$id.' nebyl nalezen');
		}
	}

	public function updateCompetition(int $id, string $title, string $category, string $type, \DateTimeInterface $from, \DateTimeInterface $to, bool $visible, bool $hasResults): void
	{
		$this->database->query("UPDATE `zavody` SET `nazev` = ?, `kategorie` = ?, `typ` = ?, `rok` = ?, `datum_od` = ?, `datum_do` = ?, `zobrazovat` = ?, `vysledky` = ? WHERE `id`= ? ",
					$title, $category, $type, $from->format('Y'), $from->format('Y-m-d'), $to->format('Y-m-d'), $visible ? 'ano' : 'ne', $hasResults ? 'ano' : 'ne', $id);
	}

	public function addCompetition(string $title, string $category, string $type, \DateTimeInterface $from, \DateTimeInterface $to, bool $visible, bool $hasResults): void {
		$this->database->query("INSERT INTO `zavody`(`nazev`, `kategorie`, `typ`, `rok`, `datum_od`, `datum_do`, `zobrazovat`, `vysledky`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
			$title, $category, $type, $from->format('Y'), $from->format('Y-m-d'), $to->format('Y-m-d'), $visible ? 'ano' : 'ne', $hasResults ? 'ano' : 'ne');
	}

	public function addVysledek($idZavodu, $idZavodnika, $tym, $cips1, $umisteni1, $cips2, $umisteni2): void
	{
		$this->database->query("INSERT INTO `zavodnici_zavody`(`id_zavodu`, `id_zavodnika`, `tym`, `cips1`, `umisteni1`, `cips2`, `umisteni2`)
			VALUES (?, ?, ?, ?, ?, ?, ?)", $idZavodu, $idZavodnika, $tym, $cips1, $umisteni1, $cips2, $umisteni2);
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
	public function loadWithMissingResults(int $selectedYear): array
	{
		$firstDayOfYear = new \DateTimeImmutable($selectedYear.'-01-01');
		$lastDayOfYear = new \DateTimeImmutable($selectedYear.'-12-31');
		$yesterday = new \DateTimeImmutable((new \DateTimeImmutable())->sub(new \DateInterval('P1D'))->format('Y-m-d'));
		$result = [];
		$competitions = $this->database->query(
			'SELECT `z`.*, `cc`.`id` `categoryId`, `cc`.`output_description` `categoryDescription`
			FROM `zavody` `z` JOIN `competition_categories` `cc` ON `z`.`kategorie` = `cc`.`id`
			WHERE `z`.`zobrazovat` = ? AND `z`.`vysledky` = ? AND `z`.`datum_od` >= ? AND `z`.`datum_do` < ?
			ORDER BY `z`.`datum_od`', 'ano', 'ne', $firstDayOfYear->format('Y-m-d'),
			min($lastDayOfYear->format('Y-m-d'), $yesterday->format('Y-m-d')))->fetchAll();
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