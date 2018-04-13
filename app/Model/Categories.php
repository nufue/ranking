<?php

namespace App\Model;

final class Categories extends Base
{

	public function getCompetitorCategoriesByYear(int $year): array
	{
		$result = [];
		$result[] = Category::fromString('muz');
		$result[] = Category::fromString('zena');
		$result[] = Category::fromString('hendikep');
		if ($year >= 2017) {
			$result[] = Category::fromString('u25');
			$result[] = Category::fromString('u25_zena');
			$result[] = Category::fromString('u20');
			$result[] = Category::fromString('u20_zena');
			$result[] = Category::fromString('u15');
			$result[] = Category::fromString('u15_zena');
		}
		if ($year <= 2016) {
			$result[] = Category::fromString('u23');
			$result[] = Category::fromString('u23_zena');
			$result[] = Category::fromString('u18');
			$result[] = Category::fromString('u18_zena');
			$result[] = Category::fromString('u14');
			$result[] = Category::fromString('u14_zena');
		}
		if ($year > 2012 && $year <= 2016) {
			$result[] = Category::fromString('u12');
			$result[] = Category::fromString('u12_zena');
		}
		if ($year <= 2012) {
			$result[] = Category::fromString('u10');
			$result[] = Category::fromString('u10_zena');
		}

		$categories = [];
		/** @var Category $r */
		foreach ($result as $r) {
			$categories[$r->getCategory()] = $r->toCzechString();
		}

		return $categories;
	}

	public function addCompetitorToCategory(int $competitor, Category $category, int $year): void
	{
		$this->database->query("INSERT INTO `zavodnici_kategorie`(`id_zavodnika`, `kategorie`, `rok`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `id_zavodnika` = `id_zavodnika`", $competitor, $category->toDbString(), $year);
	}

}