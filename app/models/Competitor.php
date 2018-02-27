<?php

namespace App\Model;

use Nette\Database\Row;

final class Competitor
{
	/** @var int */
	private $id;
	/** @var string */
	private $registration;
	/** @var string */
	private $fullName;
	/** @var bool */
	private $registered;

	private function __construct()
	{
	}

	public static function fromRow(Row $row): Competitor
	{
		$c = new Competitor();
		$c->id = $row->id;
		$c->registration = $row->registrace;
		$c->fullName = $row->cele_jmeno;
		$c->registered = $row->registrovany === 'A';
		return $c;
	}



}