<?php

namespace App\Presenters;

final class ZavodnikPresenter extends BasePresenter {

	/** @var \App\Model\Ranking @inject */
	public $zebricek;
	
	/** @var \App\Model\Teams @inject */
	public $teams;

	/** @var \App\Model\Leagues @inject */
	public $leagues;
	
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
