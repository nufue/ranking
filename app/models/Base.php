<?php

namespace App\Model;

abstract class Base extends \Nette\Object {

	public static $defaultYear = 2015;

	/** @var \Nette\Database\Context @inject */
	protected $database;
	
	public function __construct(\Nette\Database\Context $database) {
		$this->database = $database;
	}
}