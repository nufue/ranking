<?php

class CheckPresenter extends BasePresenter {

	public function renderTymy() {
		$this->template->tymy = $this->context->tymy->getTymy();
	}

	public function renderKategorie() {
		$this->template->kategorie = $this->context->kategorie->getKategorie();
		$this->template->kategoriePrevod = Kategorie::$kategorie;
	}

}