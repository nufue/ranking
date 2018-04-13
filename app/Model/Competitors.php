<?php
declare(strict_types=1);

namespace App\Model;

use App\Exceptions\CategoryForCompetitorNotFoundException;
use App\Exceptions\CategoryNotFoundException;
use App\Exceptions\CompetitorNotFoundException;
use App\Exceptions\RegistrationAlreadyExistsException;
use App\Exceptions\UnregisteredCompetitorNotFoundException;
use Nette\Database\Connection;

final class Competitors extends Base
{

	/** @var Categories */
	private $categories;

	public function __construct(Connection $database, Categories $categories)
	{
		parent::__construct($database);
		$this->categories = $categories;
	}

	/**
	 * @return Competitor[]
	 */
	public function search(string $term): array
	{
		$result = [];
		$competitors = $this->database->query("SELECT * FROM `zavodnici` WHERE `registrace` = ? OR `cele_jmeno` LIKE ?", $term, '%' . $term . '%')->fetchAll();
		foreach ($competitors as $c) {
			$result[] = Competitor::fromRow($c);
		}
		return $result;
	}

	public function getCompetitorCategory(int $year, int $competitorId): Category
	{
		$category = $this->database->query("SELECT `zk`.`kategorie` FROM `zavodnici_kategorie` `zk` WHERE `zk`.`rok` = ? AND `zk`.`id_zavodnika` = ?", $year, $competitorId)->fetchField();
		if ($category !== false) {
			return Category::fromString((string)$category);
		}
		throw new CategoryForCompetitorNotFoundException();
	}

	public function setCompetitorCategory(int $year, int $competitorId, Category $category): void
	{
		$this->database->query("INSERT INTO `zavodnici_kategorie`(`id_zavodnika`, `rok`, `kategorie`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `kategorie` = ?", $competitorId, $year, $category->toDbString(), $category->toDbString());
	}

	public function changeName(int $id, string $name): void
	{
		$this->database->query("UPDATE `zavodnici` SET `cele_jmeno` = ? WHERE `id` = ?", $name, $id);
	}

	public function changeRegistration(int $id, string $registration): void {
		$existing = $this->database->query("SELECT `cele_jmeno` FROM `zavodnici` WHERE `registrace` = ?", $registration)->fetch();
		if ($existing === false) {
			$this->database->query("UPDATE `zavodnici` SET `registrace` = ? WHERE `id` = ?", $registration, $id);
		} else {
			throw new RegistrationAlreadyExistsException('Závodník s registrací číslo '.$registration.' již v systému existuje pod jménem '.$existing->cele_jmeno);
		}
	}

	public function changeCategory(int $id, int $year, Category $category): void
	{
		$this->database->query("UPDATE `zavodnici_kategorie` SET `kategorie` = ? WHERE `id_zavodnika` = ? AND `rok` = ?", $category->toDbString(), $id, $year);
	}

	/**
	 * @return Category[]
	 */
	public function loadCategories(int $id): array
	{
		$result = [];
		$categories = $this->database->query("SELECT `rok`, `kategorie` FROM `zavodnici_kategorie` WHERE `id_zavodnika` = ?", $id)->fetchAll();
		foreach ($categories as $row) {
			$result[$row->rok] = Category::fromString($row->kategorie);
		}
		return $result;
	}

	public function getByRegistration(string $registration): Competitor
	{
		$result = $this->database->query("SELECT `id`, `cele_jmeno`, `registrace`, `registrovany` FROM `zavodnici` WHERE `registrace` = ? AND `registrovany` = 'A'", $registration)->fetch();
		if ($result !== false) {
			return Competitor::fromRow($result);
		}
		throw new CompetitorNotFoundException('Závodník s číslem registrace "' . $registration . '" nebyl nalezen.');
	}

	public function getCategoryByCompetitor(int $competitorId, int $year): Category
	{
		$category = $this->database->query("SELECT `kategorie` FROM `zavodnici_kategorie` WHERE `id_zavodnika` = ? AND `rok` = ?", $competitorId, $year)->fetchField();
		if ($category !== false) {
			return Category::fromString((string)$category);
		}
		throw new CategoryNotFoundException('Pro závodníka ID = ' . $competitorId . ' a rok = ' . $year . ' nebyla nalezena kategorie');
	}

	public function getUnregisteredIdByName(string $fullName): int
	{
		$id = $this->database->query("SELECT `id` FROM `zavodnici` WHERE `registrovany` = 'N' AND `cele_jmeno` = ?", $fullName)->fetchField();
		if ($id !== false) {
			return (int)$id;
		}
		throw new UnregisteredCompetitorNotFoundException('Neregistrovaný závodník se jménem = ' . $fullName . ' nebyl nalezen');
	}

	/**
	 * @return CompetitorInCompetition[]
	 */
	public function loadCompetitorsForCompetition(int $idCompetition): array
	{
		$result = [];
		$dbResult = $this->database->query("SELECT `z`.`id`, `z`.`registrace`, `z`.`cele_jmeno`, `z`.`registrovany`,
		`zz`.`tym`, `zk`.`kategorie`, `zz`.`cips1`, `zz`.`umisteni1`, `zz`.`cips2`, `zz`.`umisteni2`
		FROM `zavodnici` `z` JOIN `zavodnici_zavody` `zz` ON `z`.`id` = `zz`.`id_zavodnika`
		JOIN `zavodnici_kategorie` `zk` ON `z`.`id` = `zk`.`id_zavodnika`
		JOIN `zavody` ON `zavody`.`id` = `zz`.`id_zavodu`
		WHERE `zz`.`id_zavodu` = ? AND `zavody`.`rok` = `zk`.`rok`
		ORDER BY (IF(`zz`.`umisteni1` IS NULL, 0, 1) + IF(`zz`.`umisteni2` IS NULL, 0, 1)) DESC,
		(IFNULL(`zz`.`umisteni1`, 0) + IFNULL(`zz`.`umisteni2`, 0)), (IFNULL(`zz`.`cips1`, 0) + IFNULL(`zz`.`cips2`, 0)) DESC", $idCompetition)->fetchAll();
		foreach ($dbResult as $r) {
			$competitor = Competitor::fromRow($r);
			$category = Category::fromString($r->kategorie);
			$competitionResultRow = CompetitionResultRow::fromRow($r);
			$result[] = new CompetitorInCompetition($competitor, $category, $competitionResultRow);
		}
		return $result;
	}

	public function getByName(string $name): Competitor
	{
		$result = $this->database->query("SELECT `id`, `cele_jmeno`, `registrace`, `registrovany` FROM `zavodnici` WHERE `cele_jmeno` = ? AND `registrovany` = 'A'", $name)->fetch();
		if ($result !== false) {
			return Competitor::fromRow($result);
		}
		throw new CompetitorNotFoundException('Závodník se jménem "' . $name . '" nebyl nalezen.');
	}

	public function getById(int $id): Competitor
	{
		$result = $this->database->query("SELECT `id`, `cele_jmeno`, `registrace`, `registrovany` FROM `zavodnici` WHERE `id` = ?", $id)->fetch();
		if ($result !== false) {
			return Competitor::fromRow($result);
		}
		throw new CompetitorNotFoundException('Závodník s číslem registrace "' . $id . '" nebyl nalezen.');
	}

	public function getCompetitorWithCategoryById(int $id, int $year): CompetitorWithCategoryAndYear
	{
		$row = $this->database->query("SELECT `id`, `registrace`, `cele_jmeno`, `registrovany`, `kategorie` FROM `zavodnici` `z` LEFT JOIN `zavodnici_kategorie` `zk` ON `id` = `zk`.`id_zavodnika` WHERE `id` = ? AND `rok` = ?", $id, $year)->fetch();
		if ($row !== false) {
			$competitor = Competitor::fromRow($row);
			$category = Category::fromString($row->kategorie);
			return new CompetitorWithCategoryAndYear($competitor, $category, $year);
		}
		throw new CompetitorNotFoundException('Závodník s ID ' . $id . ' v roce ' . $year . ' nebyl nenalezen');
	}

	public function addNewRegisteredCompetitor(string $registrationNumber, string $fullName): int {
		$this->database->query("INSERT INTO `zavodnici`(`registrace`, `cele_jmeno`, `registrovany`) VALUES (?, ?, 'A')", $registrationNumber, $fullName);
		return (int)$this->database->getInsertId();
	}

	public function addNewUnregisteredCompetitor(string $fullName): int
	{
		$newMaximum = (int)$this->database->query("SELECT IFNULL(MAX(CAST(REPLACE(`registrace`, 'X', '') AS SIGNED) + 1), 1) `maximum` FROM `zavodnici` WHERE `registrovany` = 'N'")->fetchField();
		$this->database->query("INSERT INTO `zavodnici`(`registrace`, `cele_jmeno`, `registrovany`) VALUES (?, ?, 'N')", 'X' . $newMaximum, $fullName);
		return (int)$this->database->getInsertId();
	}


	public function getIdByRegistration(string $registration): int {
		$id = $this->database->query("SELECT `id` FROM `zavodnici` WHERE `registrace` = ?", $registration)->fetchField();
		if ($id !== false) {
			return (int)$id;
		}
		throw new CompetitorNotFoundException('Závodník s registrací = '.$registration.' nebyl nalezen');
	}

}