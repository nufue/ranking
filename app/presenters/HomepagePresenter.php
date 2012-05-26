<?php

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class HomepagePresenter extends BasePresenter {

	public function renderDefault($typ = 'celkem', $show = false) {
		$rok = 2012;
		
		if ($typ == 'celkem') $typZebricku = 'celkem';
		else if ($typ == 'u23') $typZebricku = 'junioři U23';
		else if ($typ == 'u18') $typZebricku = 'junioři U18';
		else if ($typ == 'u14') $typZebricku = 'kadeti U14';
		else if ($typ == 'zeny') $typZebricku = 'ženy';

		$this->template->typ = $typ;
		
		$this->template->typZebricku = $typZebricku;
		
		$this->template->zobrazitZavody = $show;

		$zebricek = $this->context->zebricek->getZebricek($rok, $typ);
		$this->template->datum_platnosti = $zebricek['datum_platnosti'];
		$this->template->zavody = $zebricek['zavody'];
		$this->template->zavodnici = $zebricek['zavodnici'];
	}
}
