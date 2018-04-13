<?php
declare(strict_types=1);

namespace App\Model;

final class Suggest extends Base
{

	public function getSuggest($s)
	{
		return $this->database->query("SELECT `cele_jmeno`, `registrace` FROM `zavodnici` WHERE (`cele_jmeno` LIKE ? OR `registrace` = ?) AND `registrace` NOT LIKE 'X%'", $s . '%', (int)$s)->fetchAll();
	}

}