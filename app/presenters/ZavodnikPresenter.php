<?php

namespace App\Presenters;

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class ZavodnikPresenter extends BasePresenter {

	/** @var \App\Model\Zebricek @inject */
	public $zebricek;
	
	/** @var \App\Model\Tymy @inject */
	public $tymy;
	
	public function renderDefault($id, $rok) {
		$this->template->rok = $rok;
		$detail = $this->zebricek->getZavodnikRok($id, $rok);
		$this->template->zavodnik = $detail['zavodnik'];
		$this->template->vysledky = $detail['vysledky'];
		$this->template->vysledkyCelkovy = $detail['vysledky_celkovy'];
		$this->template->vysledkyDorost = $detail['vysledky_dorost'];
		$this->template->vysledkyZeny = $detail['vysledky_zeny'];
		$this->template->ligy = \App\Model\Tymy::$ligy;
		$this->template->clenstvi = $this->tymy->getClenstvi($id, $rok)->fetchAll();
	}

}
