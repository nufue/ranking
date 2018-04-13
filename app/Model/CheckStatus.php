<?php

namespace App\Model;

final class CheckStatus {

	public const NO_PROBLEM = 1;
	public const NEW = 2;
	public const ADD_CATEGORY = 3;
	public const CATEGORIES_DIFFER = 4;
	public const NO_CATEGORY_SPECIFIED = 5;
	public const CATEGORY_NOT_ALLOWED = 6;


	/** @var int */
	private $value;

	/** @var bool */
	private $nameDifferent;

	public function __construct(int $value, bool $nameDifferent)
	{
		$this->value = $value;
		$this->nameDifferent = $nameDifferent;
	}

	public function isOk(): bool {
		return ($this->isAdd() || $this->value === self::NO_PROBLEM) && !$this->isNameDifferent();
	}

	public function isWarning(): bool {
		return $this->doCategoriesDiffer() || $this->isNameDifferent();
	}

	public function isProblem(): bool {
		return $this->hasNoCategory() || $this->isCategoryNotAllowed();
	}

	public function doCategoriesDiffer(): bool {
		return $this->value === self::CATEGORIES_DIFFER;
	}

	public function hasNoCategory(): bool {
		return $this->value === self::NO_CATEGORY_SPECIFIED;
	}

	public function isAdd(): bool {
		return $this->isNew() || $this->isAddCategory();
	}

	public function isNameDifferent(): bool {
		return $this->nameDifferent;
	}

	public function isNew(): bool {
		return $this->value === self::NEW;
	}

	public function isAddCategory(): bool {
		return $this->value === self::ADD_CATEGORY;
	}

	public function isCategoryNotAllowed(): bool {
		return $this->value === self::CATEGORY_NOT_ALLOWED;
	}

}