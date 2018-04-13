<?php
declare(strict_types=1);

namespace App\Model;

use Nette\Database\Row;

final class CompetitionResultRow
{

	/** @var string|null */
	private $team;
	/** @var int|null */
	private $cips1;
	/** @var int|null */
	private $cips2;
	/** @var float|null */
	private $rank1;
	/** @var float|null */
	private $rank2;

	private function __construct()
	{
	}

	public static function fromRow(Row $row): CompetitionResultRow {
		$r = new CompetitionResultRow();
		$r->team = $row->tym;
		$r->cips1 = $row->cips1;
		$r->cips2 = $row->cips2;
		$r->rank1 = $row->umisteni1;
		$r->rank2 = $row->umisteni2;
		return $r;
	}

	public function getTeam(): ?string
	{
		return $this->team;
	}

	public function getCips1(): ?int
	{
		return $this->cips1;
	}

	public function getCips2(): ?int
	{
		return $this->cips2;
	}

	public function getRank1(): ?float
	{
		return $this->rank1;
	}

	public function getRank2(): ?float
	{
		return $this->rank2;
	}

}