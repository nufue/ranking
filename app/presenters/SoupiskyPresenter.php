<?php

use \Nette\Application\UI\Form;

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class SoupiskyPresenter extends BasePresenter {

	public function renderDefault($rok = NULL) {
		if ($rok === NULL) $rok = self::$defaultYear;
		$this->template->ligy = Tymy::$ligy;
		$this->template->rok = $rok;
	}

	public function renderDetail($rok, $liga) {
		$this->template->nazevLigy = Tymy::$ligy[$liga];
		$this->template->rok = $rok;
		$this->template->soupisky = $this->context->tymy->getSoupiskaLiga($rok, $liga);
	}

}
