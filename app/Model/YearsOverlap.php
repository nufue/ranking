<?php

namespace App\Model;

final class YearsOverlap
{

	public function isOverlapped(?int $from1, ?int $to1, ?int $from2, ?int $to2): bool
	{
		$from1 = $from1 ?? 0;
		$from2 = $from2 ?? 0;
		$to1 = $to1 ?? 2100;
		$to2 = $to2 ?? 2100;
		return max($from1, $from2) <= min($to1, $to2);
	}

}