<?php

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class ZavodnikPresenter extends BasePresenter {

	public function renderDefault($id) {
		$rok = 2012;
		$this->template->rok = $rok;
		$detail = $this->context->zebricek->getZavodnikRok($id, $rok);
		$this->template->zavodnik = $detail['zavodnik'];
		$this->template->vysledky = $detail['vysledky'];
	}

}
