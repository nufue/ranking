<?php

namespace App\Model;

final class Team {

	/** @var int */
	private $id;
	/** @var int */
	private $year;
	/** @var string */
	private $league;
	/** @var string */
	private $name;
	/** @var string */
	private $code;
	/** @var int */
	private $competitorsCount;

	public function __construct(int $id, int $year, string $league, string $name, string $code, int $competitorsCount)
	{
		$this->id = $id;
		$this->year = $year;
		$this->league = $league;
		$this->name = $name;
		$this->code = $code;
		$this->competitorsCount = $competitorsCount;
	}

	public function getId(): int {
		return $this->id;
	}

	public function getCode(): string {
		return $this->code;
	}

	public function getLeague(): string {
		return $this->league;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getCompetitorsCount(): int {
		return $this->competitorsCount;
	}

}