<?php

namespace App\Presenters;

use App\Model\Leagues;
use App\Model\Teams;

final class SoupiskyPresenter extends BasePresenter {

	/** @var Teams @inject */
	public $teams;

	/** @var Leagues @inject */
	public $leagues;
	
	public function renderDefault($rok = NULL) {
		if ($rok === NULL) {
			$rok = $this->defaultYear->getDefaultYear();
		}
		$this->template->ligy = $this->leagues->getLeagues();
		$this->template->rok = $rok;
	}

	public function renderDetail($rok, $liga) {
		$this->template->nazevLigy = $this->leagues->getName($liga);
		$this->template->rok = $rok;
		$this->template->soupisky = $this->teams->loadRoasterForLeague($rok, $liga);
	}

}
