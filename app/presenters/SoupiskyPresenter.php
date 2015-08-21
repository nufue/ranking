<?php

namespace App\Presenters;

final class SoupiskyPresenter extends BasePresenter {

	/** @var \App\Model\Tymy @inject */
	public $tymy;
	
	public function renderDefault($rok = NULL) {
		if ($rok === NULL) $rok = self::$defaultYear;
		$this->template->ligy = \App\Model\Tymy::$ligy;
		$this->template->rok = $rok;
	}

	public function renderDetail($rok, $liga) {
		$this->template->nazevLigy = \App\Model\Tymy::$ligy[$liga];
		$this->template->rok = $rok;
		$this->template->soupisky = $this->tymy->getSoupiskaLiga($rok, $liga);
	}

}
