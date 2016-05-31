<?php

namespace App\Model;

class Competitors extends Base
{
	public function search($term)
	{
		return $this->database->query("SELECT * FROM `zavodnici` WHERE `registrace` = ? OR `cele_jmeno` LIKE ?", $term, '%'.$term.'%')->fetchAll();
	}
}