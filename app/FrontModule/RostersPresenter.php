<?php
declare(strict_types=1);

namespace App\FrontModule\Presenters;

use App\Model\Leagues;
use App\Model\TeamMembersCount;
use App\Model\Teams;

final class RostersPresenter extends BasePresenter
{

	/** @var Teams */
	private $teams;

	/** @var Leagues */
	private $leagues;

	/** @var TeamMembersCount */
	private $teamMembersCount;

	public function __construct(Teams $teams, Leagues $leagues, TeamMembersCount $teamMembersCount)
	{
		parent::__construct();
		$this->teams = $teams;
		$this->leagues = $leagues;
		$this->teamMembersCount = $teamMembersCount;
	}

	public function renderDefault(string $year): void
	{
		$this->template->leagues = $this->leagues->getLeaguesForYear((int)$year);
		$this->template->year = $year;
	}

	public function renderDetail(string $year, string $liga): void
	{
		$this->template->leagueName = $this->leagues->getName($liga);
		$this->template->year = $year;
		$this->template->rosters = $this->teams->loadRoasterForLeague($year, $liga);
		$this->template->teamMembersMaxCount = $this->teamMembersCount->getByYear((int)$year);
	}

}
