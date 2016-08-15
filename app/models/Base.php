<?php

namespace App\Model;

use Nette\Database\Context;
use Nette\Object;

abstract class Base extends Object {

	/** @var int */
	public static $defaultYear = 2016;

	/** @var Context */
	protected $database;
	
	public function __construct(Context $database) {
		$this->database = $database;
	}
}