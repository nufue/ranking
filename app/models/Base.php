<?php

namespace App\Model;

use Nette\Database\Context;

abstract class Base {

	/** @var Context */
	protected $database;
	
	public function __construct(Context $database) {
		$this->database = $database;
	}
}