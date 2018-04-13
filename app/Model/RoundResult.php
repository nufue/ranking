<?php

namespace App\Model;

final class RoundResult
{
	/** @var int */
	private $round;
	/** @var int */
	private $cips;
	/** @var float */
	private $rank;

	public function __construct(int $round, int $cips, float $rank)
	{
		$this->round = $round;
		$this->cips = $cips;
		$this->rank = $rank;
	}

	public function getRound(): int
	{
		return $this->round;
	}

	public function getCips(): int
	{
		return $this->cips;
	}

	public function getRank(): float
	{
		return $this->rank;
	}

	public function getFormattedRank(): string
	{
		if (abs($this->rank - (int)$this->rank) < 0.001) {
			return number_format($this->rank, 0, ',', '');
		} else {
			return number_format($this->rank, 1, ',', '');
		}
	}

}