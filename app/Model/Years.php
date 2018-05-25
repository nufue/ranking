<?php

namespace App\Model;

final class Years extends Base
{

	public function loadAll(): array
	{
		return $this->database->query("SELECT DISTINCT `rok` FROM `zavody` ORDER BY `rok`")->fetchPairs('rok', 'rok');
	}
	
}