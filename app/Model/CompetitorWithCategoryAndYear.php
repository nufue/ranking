<?php
declare(strict_types=1);

namespace App\Model;

final class CompetitorWithCategoryAndYear
{
	/** @var Competitor */
	private $competitor;
	/** @var Category */
	private $category;
	/** @var int */
	private $year;

	public function __construct(Competitor $competitor, Category $category, int $year)
	{
		$this->competitor = $competitor;
		$this->category = $category;
		$this->year = $year;
	}

	public function getCategory(): Category {
		return $this->category;
	}

	public function getId(): int {
		return $this->competitor->getId();
	}

	public function getRegistration(): string {
		return $this->competitor->getRegistration();
	}

	public function getFullName(): string {
		return $this->competitor->getFullName();
	}

	public function isRegistered(): bool {
		return $this->competitor->isRegistered();
	}

	public function getYear(): int {
		return $this->year;
	}

}