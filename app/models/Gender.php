<?php

namespace App\Model;

final class Gender
{

	const MALE = 'male';
	const FEMALE = 'female';

	private $gender;

	/**
	 * Gender constructor.
	 * @param string $gender
	 */
	private function __construct($gender)
	{
		$this->gender = $gender;
	}

	/**
	 * @param string $value
	 * @return Gender
	 */
	public static function fromString(string $value)
	{
		if (mb_strtolower($value) === 'm')
			return new Gender(Gender::MALE);
		else if (mb_strtolower($value) === 'z' || mb_strtolower($value) === 'f')
			return new Gender(Gender::FEMALE);
		else
			throw new \LogicException('Neznámá hodnota pohlaví.');
	}

	/**
	 * @return bool
	 */
	public function isMale(): bool
	{
		return $this->gender === self::MALE;
	}

	/**
	 * @return bool
	 */
	public function isFemale(): bool
	{
		return $this->gender === self::FEMALE;
	}

}