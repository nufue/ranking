<?php

namespace App\Model;

final class CheckedResultRow
{

	/** @var string */
	private $registration;
	/** @var string */
	private $fullName;
	/** @var string */
	private $team;
	/** @var Category|null */
	private $category;
	/** @var CheckStatus */
	private $status;
	/** @var Category|null */
	private $differingCategory = null;
	/** @var string|null */
	private $differingName = null;

	/** @var RoundResult[] */
	private $results = [];

	public function __construct(string $registration, string $fullName, string $team, ?Category $category, CheckStatus $status)
	{
		$this->registration = $registration;
		$this->fullName = $fullName;
		$this->team = $team;
		$this->category = $category;
		$this->status = $status;
	}

	public function addRoundResult(RoundResult $result): void
	{
		$this->results[$result->getRound()] = $result;
	}

	public function setDifferingCategory(Category $category): void
	{
		$this->differingCategory = $category;
	}

	public function setDifferingName(string $fullName): void
	{
		$this->differingName = $fullName;
	}

	public function hasRound(int $round): bool
	{
		return isset($this->results[$round]);
	}

	public function getRound(int $round): RoundResult
	{
		return $this->results[$round];
	}

	public function getRegistration(): string
	{
		return $this->registration;
	}

	public function getFullName(): string
	{
		return $this->fullName;
	}

	public function getTeam(): string
	{
		return $this->team;
	}

	public function hasCategory(): bool
	{
		return $this->category !== null;
	}

	public function getCategory(): ?Category
	{
		return $this->category;
	}

	public function getStatus(): CheckStatus
	{
		return $this->status;
	}

	public function getDifferingCategory(): ?Category
	{
		return $this->differingCategory;
	}

	public function getDifferingName(): ?string {
		return $this->differingName;
	}

}