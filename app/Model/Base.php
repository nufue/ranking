<?php

namespace App\Model;

use Nette\Database\Connection;

abstract class Base {

	/** @var Connection */
	protected $database;
	
	public function __construct(Connection $database) {
		$this->database = $database;
	}
}