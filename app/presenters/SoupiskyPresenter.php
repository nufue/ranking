<?php

namespace App\Presenters;

use App\Model\Leagues;
use App\Model\Teams;

final class SoupiskyPresenter extends BasePresenter
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

	public function renderDefault($rok = null): void
	{
		if ($rok === null) {
			$rok = $this->defaultYear->getDefaultYear();
		}
		$this->template->ligy = $this->leagues->getLeagues();
		$this->template->rok = $rok;
	}

	public function renderDetail($rok, $liga): void
	{
		$this->template->nazevLigy = $this->leagues->getName($liga);
		$this->template->rok = $rok;
		$this->template->soupisky = $this->teams->loadRoasterForLeague($rok, $liga);
	}

}
