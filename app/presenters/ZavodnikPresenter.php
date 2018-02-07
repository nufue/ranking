<?php

namespace App\Presenters;

use App\Model\Leagues;
use App\Model\Ranking;
use App\Model\Teams;

final class ZavodnikPresenter extends BasePresenter {

	/** @var \App\Model\Ranking */
	private $zebricek;
	
	/** @var \App\Model\Teams */
	private $teams;

	/** @var \App\Model\Leagues */
	private $leagues;

	public function __construct(Ranking $ranking, Teams $teams, Leagues $leagues)
	{
		parent::__construct();
		$this->zebricek = $ranking;
		$this->teams = $teams;
		$this->leagues = $leagues;
	}
	
	public function renderDefault($id, $rok) {
		$this->template->rok = $rok;
		$detail = $this->zebricek->getZavodnikRok($id, $rok);
		$this->template->zavodnik = $detail['zavodnik'];
		$this->template->vysledky = $detail['vysledky'];
		$this->template->vysledkyCelkovy = $detail['vysledky_celkovy'];
		$this->template->vysledkyDorost = $detail['vysledky_dorost'];
		$this->template->vysledkyZeny = $detail['vysledky_zeny'];
		$this->template->ligy = $this->leagues->getLeagues();
		$this->template->clenstvi = $this->teams->loadTeamMembership($id, $rok);
	}

}
