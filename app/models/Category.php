<?php

namespace App\Model;

use App\Exceptions\CategoriesForThisYearAreNotSetException;

final class Category
{

	const MEN = 'men';
	const WOMEN = 'women';
	const HANDICAPPED = 'handicapped';
	const U10_MEN = 'u10';
	const U12_MEN = 'u12';
	const U14_MEN = 'u14';
	const U15_MEN = 'u15';
	const U18_MEN = 'u18';
	const U20_MEN = 'u20';
	const U23_MEN = 'u23';
	const U25_MEN = 'u25';
	const U10_WOMEN = 'u10_women';
	const U12_WOMEN = 'u12_women';
	const U14_WOMEN = 'u14_women';
	const U15_WOMEN = 'u15_women';
	const U18_WOMEN = 'u18_women';
	const U20_WOMEN = 'u20_women';
	const U23_WOMEN = 'u23_women';
	const U25_WOMEN = 'u25_women';

	private $category;

	private function __construct(string $value)
	{
		$this->category = $value;
	}

	public function getCategory(): string
	{
		return $this->category;
	}

	public static function fromString(string $input): Category
	{
		$input = mb_strtolower($input);
		if ($input == 'm' || $input == 'muz')
			return new Category(Category::MEN);
		else if ($input == 'ž' || $input == 'z' || $input == 'zena')
			return new Category(Category::WOMEN);
		else if ($input == 'u10' || $input == 'u 10')
			return new Category(Category::U10_MEN);
		else if ($input == 'u12' || $input == 'u 12')
			return new Category(Category::U12_MEN);
		else if ($input == 'u14' || $input == 'u 14')
			return new Category(Category::U14_MEN);
		else if ($input == 'u15' || $input == 'u 15')
			return new Category(Category::U15_MEN);
		else if ($input == 'u18' || $input == 'u 18')
			return new Category(Category::U18_MEN);
		else if ($input == 'u20' || $input == 'u 20')
			return new Category(Category::U20_MEN);
		else if ($input == 'u23' || $input == 'u 23')
			return new Category(Category::U23_MEN);
		else if ($input == 'u25' || $input == 'u 25')
			return new Category(Category::U25_MEN);
		else if ($input == 'u10ž' || $input == 'u10 ž' || $input == 'u10_zena')
			return new Category(Category::U10_WOMEN);
		else if ($input == 'u12ž' || $input == 'u12 ž' || $input == 'u12_zena')
			return new Category(Category::U12_WOMEN);
		else if ($input == 'u14ž' || $input == 'u14 ž' || $input == 'u14_zena')
			return new Category(Category::U14_WOMEN);
		else if ($input == 'u15ž' || $input == 'u15 ž' || $input == 'u15_zena')
			return new Category(Category::U15_WOMEN);
		else if ($input == 'u18ž' || $input == 'u18 ž' || $input == 'u18_zena')
			return new Category(Category::U18_WOMEN);
		else if ($input == 'u20ž' || $input == 'u20 ž' || $input == 'u20_zena')
			return new Category(Category::U20_WOMEN);
		else if ($input == 'u23ž' || $input == 'u23 ž' || $input == 'u23_zena')
			return new Category(Category::U23_WOMEN);
		else if ($input == 'u25ž' || $input == 'u25 ž' || $input == 'u25_zena')
			return new Category(Category::U25_WOMEN);
		else if ($input == 'h' || $input == 'hendikep')
			return new Category(Category::HANDICAPPED);
		else
			throw new \LogicException('Neznámá hodnota kategorie.');
	}

	public static function determine(int $currentYear, int $birthYear, Gender $gender): Category
	{
		if ($currentYear >= 2017) {
			$diff = $currentYear - $birthYear;
			if ($diff <= 15) {
				if ($gender->isMale())
					return new Category(Category::U15_MEN);
				else
					return new Category(Category::U15_WOMEN);
			} else if ($diff <= 20) {
				if ($gender->isMale())
					return new Category(Category::U20_MEN);
				else
					return new Category(Category::U20_WOMEN);
			} else if ($diff <= 25) {
				if ($gender->isMale())
					return new Category(Category::U25_MEN);
				else
					return new Category(Category::U25_WOMEN);
			} else {
				if ($gender->isMale())
					return new Category(Category::MEN);
				else
					return new Category(Category::WOMEN);
			}
		} else {
			throw new CategoriesForThisYearAreNotSetException('Kategorie pro tento rok nejsou nastaveny.');
		}
	}

	public function getDbCategory(): string {
		if ($this->category === self::MEN)
			return 'muz';
		else if ($this->category === self::WOMEN)
			return 'zena';
		else if ($this->category === self::U15_MEN)
			return 'u15';
		else if ($this->category === self::U15_WOMEN)
			return 'u15_zena';
		else if ($this->category === self::U20_MEN)
			return 'u20';
		else if ($this->category === self::U20_WOMEN)
			return 'u20_zena';
		else if ($this->category === self::U25_MEN)
			return 'u25';
		else if ($this->category === self::U25_WOMEN)
			return 'u25_zena';
		else if ($this->category === self::HANDICAPPED)
			return 'hendikep';
	}

}