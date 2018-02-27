<?php

namespace App\Model;

use App\Exceptions\CompetitorNotFoundException;
use App\Exceptions\RegistrationForCompetitorNotFoundException;

final class Competitors extends Base
{
	/**
	 * @param $term
	 * @return Competitor[]
	 */
	public function search($term): array
	{
		$result = [];
		$competitors = $this->database->query("SELECT * FROM `zavodnici` WHERE `registrace` = ? OR `cele_jmeno` LIKE ?", $term, '%' . $term . '%')->fetchAll();
		foreach ($competitors as $c) {
			$result[] = Competitor::fromRow($c);
		}
		return $result;
	}

	public function getById(int $id): Registration
	{
		$registration = $this->database->query("SELECT `registrace` FROM `zavodnici` WHERE `id` = ?", $id)->fetchField();
		if ($registration !== false) {
			$row = $this->database->query("SELECT * FROM `registrace` WHERE `registrace` = ?", $registration)->fetch();
			if ($row !== false) {
				return Registration::createFromRow($row);
			} else {
				throw new RegistrationForCompetitorNotFoundException();
			}
		} else {
			throw new CompetitorNotFoundException();
		}
	}

	public function getCompetitorCategory(int $year, int $registration): Category
	{
		$category = $this->database->query("SELECT `zk`.`kategorie` FROM `zavodnici_kategorie` `zk` JOIN `zavodnici` `z` ON `zk`.`id_zavodnika` = `z`.`id` WHERE `zk`.`rok` = ? AND `z`.`registrace` = ?", $year, $registration)->fetchField();
		if ($category === false) {
			$row = $this->database->query("SELECT * FROM `registrace` WHERE `registrace` = ?", $registration)->fetch();
			if ($row !== false) {
				return Category::determine($year, (int)$row->rok_narozeni, Gender::fromString($row->pohlavi));
			} else {
				throw new RegistrationForCompetitorNotFoundException();
			}
		} else {
			return Category::fromString($category);
		}
	}

	public function setCompetitorCategory(int $year, int $registration, Category $category): void
	{
		$id = $this->database->query("SELECT `id` FROM `zavodnici` WHERE `registrace` = ?", $registration)->fetchField();
		if ($id !== false) {
			$this->database->query("INSERT INTO `zavodnici_kategorie`(`id_zavodnika`, `rok`, `kategorie`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `kategorie` = ?", $id, $year, $category->getDbCategory(), $category->getDbCategory());
		}
	}
}