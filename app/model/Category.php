<?php
declare(strict_types=1);

namespace App\Model;

use App\Exceptions\NotUCategoryException;
use App\Exceptions\UnknownCategoryException;

final class Category
{
	private const MEN = 'men';
	private const WOMEN = 'women';
	private const HANDICAPPED = 'handicapped';
	private const U10_MEN = 'u10';
	private const U10_WOMEN = 'u10_women';
	private const U12_MEN = 'u12';
	private const U12_WOMEN = 'u12_women';
	private const U14_MEN = 'u14';
	private const U14_WOMEN = 'u14_women';
	private const U15_MEN = 'u15';
	private const U15_WOMEN = 'u15_women';
	private const U18_MEN = 'u18';
	private const U18_WOMEN = 'u18_women';
	private const U20_MEN = 'u20';
	private const U20_WOMEN = 'u20_women';
	private const U23_MEN = 'u23';
	private const U23_WOMEN = 'u23_women';
	private const U25_MEN = 'u25';
	private const U25_WOMEN = 'u25_women';

	private $category;

	private function __construct(string $value)
	{
		$this->category = $value;
	}

	public function getCategory(): string
	{
		return $this->category;
	}

	public function isWomen(): bool
	{
		return mb_substr($this->category, -5) === 'women';
	}

	public function isU(): bool
	{
		return mb_substr($this->category, 0, 1) === 'u';
	}

	/**
	 * @throws NotUCategoryException
	 */
	public function getBaseForU(): string
	{
		if ($this->isU()) {
			return mb_substr($this->category, 0, 3);
		}
		throw new NotUCategoryException();
	}

	/**
	 * @throws UnknownCategoryException
	 */
	public static function fromString(?string $input): Category
	{
		$input = str_replace(['ž', ' '], ['z', ''], mb_strtolower($input));
		if (\in_array($input, ['m', 'muz', self::MEN], true))
			return new Category(self::MEN);
		else if (\in_array($input, ['z', 'zena', self::WOMEN], true))
			return new Category(self::WOMEN);
		else if ($input === 'u10' || $input === self::U10_MEN)
			return new Category(self::U10_MEN);
		else if ($input === 'u12' || $input === self::U12_MEN)
			return new Category(self::U12_MEN);
		else if ($input === 'u14' || $input === self::U14_MEN)
			return new Category(self::U14_MEN);
		else if ($input === 'u15' || $input === self::U15_MEN)
			return new Category(self::U15_MEN);
		else if ($input === 'u18' || $input === self::U18_MEN)
			return new Category(self::U18_MEN);
		else if ($input === 'u20' || $input === self::U20_MEN)
			return new Category(self::U20_MEN);
		else if ($input === 'u23' || $input === self::U23_MEN)
			return new Category(self::U23_MEN);
		else if ($input === 'u25' || $input === self::U25_MEN)
			return new Category(self::U25_MEN);
		else if ($input === 'u10z' || $input === self::U10_WOMEN || $input === 'u10_zena')
			return new Category(self::U10_WOMEN);
		else if ($input === 'u12z' || $input === self::U12_WOMEN || $input === 'u12_zena')
			return new Category(self::U12_WOMEN);
		else if ($input === 'u14z' || $input === self::U14_WOMEN || $input === 'u14_zena')
			return new Category(self::U14_WOMEN);
		else if ($input === 'u15z' || $input === self::U15_WOMEN || $input === 'u15_zena')
			return new Category(self::U15_WOMEN);
		else if ($input === 'u18z' || $input === self::U18_WOMEN || $input === 'u18_zena')
			return new Category(self::U18_WOMEN);
		else if ($input === 'u20z' ||$input ===  self::U20_WOMEN || $input === 'u20_zena')
			return new Category(self::U20_WOMEN);
		else if ($input === 'u23z' || $input === self::U23_WOMEN || $input === 'u23_zena')
			return new Category(self::U23_WOMEN);
		else if ($input === 'u25z' || $input === self::U25_WOMEN || $input === 'u25_zena')
			return new Category(self::U25_WOMEN);
		else if ($input === 'h' || $input === 'hendikep' || $input === self::HANDICAPPED)
			return new Category(self::HANDICAPPED);
		else
			throw new UnknownCategoryException('Neznámá hodnota kategorie [' . $input . '].');
	}

	public function toCzechString(): string
	{
		$categories = [
			self::MEN => 'muži',
			self::WOMEN => 'ženy',
			self::HANDICAPPED => 'hendikepovaní',
			self::U10_MEN => 'U10',
			self::U10_WOMEN => 'U10 dívky',
			self::U12_MEN => 'U12',
			self::U12_WOMEN => 'U12 dívky',
			self::U14_MEN => 'U14',
			self::U14_WOMEN => 'U14 ženy',
			self::U15_MEN => 'U15',
			self::U15_WOMEN => 'U15 dívky',
			self::U18_MEN => 'U18',
			self::U18_WOMEN => 'U18 ženy',
			self::U20_MEN => 'U20',
			self::U20_WOMEN => 'U20 ženy',
			self::U23_MEN => 'U23',
			self::U23_WOMEN => 'U23 ženy',
			self::U25_MEN => 'U25',
			self::U25_WOMEN => 'U25 ženy',
		];
		return $categories[$this->category];
	}

	public function toDbString(): string
	{
		$categories = [
			self::MEN => 'muz',
			self::WOMEN => 'zena',
			self::HANDICAPPED => 'hendikep',
			self::U10_MEN => 'u10',
			self::U10_WOMEN => 'u10_zena',
			self::U12_MEN => 'u12',
			self::U12_WOMEN => 'u12_zena',
			self::U14_MEN => 'u14',
			self::U14_WOMEN => 'u14_zena',
			self::U15_MEN => 'u15',
			self::U15_WOMEN => 'u15_zena',
			self::U18_MEN => 'u18',
			self::U18_WOMEN => 'u18_zena',
			self::U20_MEN => 'u20',
			self::U20_WOMEN => 'u20_zena',
			self::U23_MEN => 'u23',
			self::U23_WOMEN => 'u23_zena',
			self::U25_MEN => 'u25',
			self::U25_WOMEN => 'u25_zena',
		];
		return $categories[$this->category];
	}

	public function toShortString(): string {
		$categories = [
			self::MEN => 'M',
			self::WOMEN => 'Ž',
			self::HANDICAPPED => 'H',
			self::U10_MEN => 'U10',
			self::U10_WOMEN => 'U10Ž',
			self::U12_MEN => 'U12',
			self::U12_WOMEN => 'U12Ž',
			self::U14_MEN => 'U14',
			self::U14_WOMEN => 'U14Ž',
			self::U15_MEN => 'U15',
			self::U15_WOMEN => 'U15Ž',
			self::U18_MEN => 'U18',
			self::U18_WOMEN => 'U18Ž',
			self::U20_MEN => 'U20',
			self::U20_WOMEN => 'U20Ž',
			self::U23_MEN => 'U23',
			self::U23_WOMEN => 'U23Ž',
			self::U25_MEN => 'U25',
			self::U25_WOMEN => 'U25Ž',
		];
		return $categories[$this->category];
	}

}