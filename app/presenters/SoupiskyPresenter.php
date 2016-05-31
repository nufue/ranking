<?php

namespace App\Presenters;

use App\Model\Tymy;

final class SoupiskyPresenter extends BasePresenter {

	/** @var Tymy @inject */
	public $teams;
	
	public function renderDefault($rok = NULL) {
		if ($rok === NULL) $rok = self::$defaultYear;
		$this->template->ligy = Tymy::$leagues;
		$this->template->rok = $rok;
	}

	public function renderDetail($rok, $liga) {
		$this->template->nazevLigy = Tymy::$leagues[$liga];
		$this->template->rok = $rok;
		$this->template->soupisky = $this->teams->getSoupiskaLiga($rok, $liga);
	}

}
