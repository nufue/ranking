<?php
declare(strict_types=1);

namespace App\Model;

final class DefaultYear
{

	/** @var int */
	private $defaultYear;

	public function getDefaultYear(): int
	{
		return $this->defaultYear;
	}

	public function setDefaultYear(int $defaultYear): void
	{
		$this->defaultYear = $defaultYear;
	}

}