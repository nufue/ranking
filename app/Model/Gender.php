<?php
declare(strict_types=1);

namespace App\Model;

use App\Exceptions\UnknownGenderException;

final class Gender
{

	private const MALE = 'male';
	private const FEMALE = 'female';

	private $gender;

	private function __construct(string $gender)
	{
		$this->gender = $gender;
	}

	public static function fromString(string $value): Gender
	{
		if (mb_strtolower($value) === 'm')
			return new Gender(Gender::MALE);
		else if (mb_strtolower($value) === 'z' || mb_strtolower($value) === 'f')
			return new Gender(Gender::FEMALE);
		else
			throw new UnknownGenderException('Neznámá hodnota pohlaví.');
	}

	public function isMale(): bool
	{
		return $this->gender === self::MALE;
	}

	public function isFemale(): bool
	{
		return $this->gender === self::FEMALE;
	}

}