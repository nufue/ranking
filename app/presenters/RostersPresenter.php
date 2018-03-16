<?php
declare(strict_types=1);

namespace App\Presenters;

use App\Model\Leagues;
use App\Model\Teams;

final class RostersPresenter extends BasePresenter
{

	/** @var Teams */
	private $teams;

	/** @var Leagues */
	private $leagues;

	public function __construct(Teams $teams, Leagues $leagues)
	{
		parent::__construct();
		$this->teams = $teams;
		$this->leagues = $leagues;
	}

	public function renderDefault($year): void
	{
		$this->template->leagues = $this->leagues->getLeaguesForYear((int)$year);
		$this->template->year = $year;
	}

	public function renderDetail($year, $liga): void
	{
		$this->template->leagueName = $this->leagues->getName($liga);
		$this->template->year = $year;
		$this->template->rosters = $this->teams->loadRoasterForLeague($year, $liga);
	}

}
