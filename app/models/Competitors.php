<?php

namespace App\Model;

use App\Exceptions\CompetitorNotFound;
use App\Exceptions\RegistrationForCompetitorNotFound;

final class Competitors extends Base
{
	public function search($term)
	{
		return $this->database->query("SELECT * FROM `zavodnici` WHERE `registrace` = ? OR `cele_jmeno` LIKE ?", $term, '%' . $term . '%')->fetchAll();
	}

	public function getById(int $id): Competitor
	{
		$registration = $this->database->query("SELECT `registrace` FROM `zavodnici` WHERE `id` = ?", $id)->fetchField();
		if ($registration !== FALSE) {
			$row = $this->database->query("SELECT * FROM `registrace` WHERE `registrace` = ?", $registration)->fetch();
			if ($row !== FALSE) {
				return Competitor::createFromRow($row);
			} else {
				throw new RegistrationForCompetitorNotFound();
			}
		} else {
			throw new CompetitorNotFound();
		}
	}

	public function getCompetitorCategory(int $year, int $registration): Category
	{
		$category = $this->database->query("SELECT `zk`.`kategorie` FROM `zavodnici_kategorie` `zk` JOIN `zavodnici` `z` ON `zk`.`id_zavodnika` = `z`.`id` WHERE `zk`.`rok` = ? AND `z`.`registrace` = ?", $year, $registration)->fetchField();
		if ($category === FALSE) {
			$row = $this->database->query("SELECT * FROM `registrace` WHERE `registrace` = ?", $registration)->fetch();
			if ($row !== FALSE) {
				return Category::determine($year, (int)$row->rok_narozeni, Gender::fromString($row->pohlavi));
			} else {
				throw new RegistrationForCompetitorNotFound();
			}
		} else {
			return Category::fromString($category);
		}
	}

	public function setCompetitorCategory(int $year, int $registration, Category $category): void
	{
		$id = $this->database->query("SELECT `id` FROM `zavodnici` WHERE `registrace` = ?", $registration)->fetchField();
		if ($id !== FALSE) {
			$this->database->query("INSERT INTO `zavodnici_kategorie`(`id_zavodnika`, `rok`, `kategorie`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `kategorie` = ?", $id, $year, $category->getDbCategory(), $category->getDbCategory());
		}
	}
}