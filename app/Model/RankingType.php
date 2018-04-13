<?php

namespace App\Model;

final class RankingType
{

	public const TOTAL = 'celkem';
	public const EXCEL = 'excel';
	public const U25 = 'u25';
	public const U23 = 'u23';
	public const U20 = 'u20';
	public const U18 = 'u18';
	public const U15 = 'u15';
	public const U14 = 'u14';
	public const U12 = 'u12';
	public const U10 = 'u10';
	public const WOMEN = 'zeny';
	public const HANDICAPPED = 'hendikepovani';

	/** @var string */
	private $value;

	public function __construct(string $value)
	{
		$this->value = $value;
	}

	public function getValue(): string {
		return $this->value;
	}

	public function getDbCategoryValues(): array
	{
		$result = [];
		if ($this->value === self::U25) {
			$result = ['u25', 'u25_zena'];
		} else if ($this->value === self::U23) {
			$result = ['u23', 'u23_zena'];
		} else if ($this->value === self::U20) {
			$result = ['u20', 'u20_zena'];
		} else if ($this->value === self::U18) {
			$result = ['u18', 'u18_zena'];
		} else if ($this->value === self::U15) {
			$result = ['u15', 'u15_zena'];
		} else if ($this->value === self::U14) {
			$result = ['u14', 'u14_zena'];
		} else if ($this->value === self::U12) {
			$result = ['u12', 'u12_zena'];
		} else if ($this->value === self::U10) {
			$result = ['u10', 'u10_zena'];
		} else if ($this->value === self::HANDICAPPED) {
			$result = ['hendikep'];
		}
		return $result;
	}


}