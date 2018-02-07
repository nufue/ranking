<?php

namespace App\Model;

use App\Exceptions\LeagueNotFoundException;

final class Leagues
{

	private $leagues = ['1' => '1. liga', '2a' => '2. liga, sk. A', '2b' => '2. liga, sk. B', '2c' => '2. liga, sk. C'];

	public function getLeagues(): array
	{
		return $this->leagues;
	}

	public function getName($league): string
	{
		if (isset($this->leagues[$league]))
			return $this->leagues[$league];
		throw new LeagueNotFoundException();
	}

}