<?php

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class ZavodnikPresenter extends BasePresenter {

	public function renderDefault($id, $rok) {
		$this->template->rok = $rok;
		$detail = $this->context->zebricek->getZavodnikRok($id, $rok);
		$this->template->zavodnik = $detail['zavodnik'];
		$this->template->vysledky = $detail['vysledky'];
		$this->template->vysledkyCelkovy = $detail['vysledky_celkovy'];
		$this->template->vysledkyDorost = $detail['vysledky_dorost'];
		$this->template->vysledkyZeny = $detail['vysledky_zeny'];
		$this->template->ligy = Tymy::$ligy;
		$this->template->clenstvi = $this->context->tymy->getClenstvi($id, $rok)->fetchAll();
	}

}
