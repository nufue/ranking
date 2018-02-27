<?php

namespace App\Model;

final class Registration
{
	/** @var int */
	private $registration;
	/** @var string */
	private $lastName;
	/** @var string */
	private $firstName;
	/** @var string */
	private $suffix;
	/** @var Gender */
	private $gender;
	/** @var int */
	private $birthYear;

	private function __construct()
	{
	}

	public static function createFromRow(\Nette\Database\IRow $row): Registration
	{
		$competitor = new Registration();
		$competitor->registration = $row->registrace;
		$competitor->lastName = $row->prijmeni;
		$competitor->firstName = $row->jmeno;
		$competitor->suffix = $row->titul;
		$competitor->gender = Gender::fromString($row->pohlavi);
		$competitor->birthYear = $row->rok_narozeni;
		return $competitor;
	}

	public function getRegistration(): int
	{
		return $this->registration;
	}

	public function getLastName(): string
	{
		return $this->lastName;
	}

	public function getFirstName(): string
	{
		return $this->firstName;
	}

	public function getSuffix(): string
	{
		return $this->suffix;
	}

	public function getGender(): Gender
	{
		return $this->gender;
	}

	public function getBirthYear(): int
	{
		return $this->birthYear;
	}



}