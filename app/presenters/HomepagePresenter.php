<?php

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class HomepagePresenter extends BasePresenter {

	public function renderDefault($show = false) {
		$rok = 2012;

		$this->template->zobrazitZavody = $show;
		$this->template->kategoriePrevod = Kategorie::$kategorie;

		$zebricek = $this->context->zebricek->getZebricek($rok);
		$this->template->datum_platnosti = $zebricek['datum_platnosti'];
		$this->template->zavody = $zebricek['zavody'];
		$this->template->zavodnici = $zebricek['zavodnici'];
	}
	
	public function renderDetail($id) {
		$rok = 2012;
		$detaily = $this->context->zavodnici->getRok($id, $rok);
	}

}
