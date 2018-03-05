<?php
declare(strict_types=1);

namespace App\Model;

use Nette\Database\Row;

final class Competition
{

	/** @var int */
	private $id;
	/** @var int */
	private $year;
	/** @var string */
	private $title;
	/** @var string */
	private $category;
	/** @var string */
	private $type;
	/** @var \DateTimeInterface */
	private $from;
	/** @var \DateTimeInterface */
	private $to;
	/** @var bool */
	private $visible;
	/** @var bool */
	private $results;

	private function __construct()
	{
	}

	public static function fromRow(Row $row): Competition
	{
		$c = new Competition();
		$c->id = (int)$row->id;
		$c->year = (int)$row->rok;
		$c->title = $row->nazev;
		$c->category = $row->kategorie ?? '';
		$c->type = $row->typ;
		$c->from = $row->datum_od;
		$c->to = $row->datum_do;
		$c->visible = $row->zobrazovat === 'ano';
		$c->results = $row->vysledky === 'ano';
		return $c;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function getTitleWithCategory(): string
	{
		return trim($this->title . ' ' . $this->category);
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getCategory(): string
	{
		return $this->category;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function isVisible(): bool
	{
		return $this->visible;
	}

	public function hasResults(): bool
	{
		return $this->results;
	}

	public function getYear(): int
	{
		return $this->year;
	}

	public function getFrom(): \DateTimeInterface
	{
		return $this->from;
	}

	public function getTo(): \DateTimeInterface
	{
		return $this->to;
	}

	public function getFormattedDates(): string
	{
		if ($this->from->format('Y-m-d') === $this->to->format('Y-m-d')) {
			return $this->from->format('j. n. Y');
		} else if ($this->from->format('n') === $this->to->format('n')) {
			return $this->from->format('j.') . ' - ' . $this->to->format('j. n. Y');
		} else {
			return $this->from->format('j. n. Y') . ' - ' . $this->to->format('j. n. Y');
		}
	}

}