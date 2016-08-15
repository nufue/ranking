<?php

namespace App\Presenters;

final class CheckPresenter extends BasePresenter {
	
	/** @var \App\Model\Tymy @inject */
	public $tymy;
	
	/** @var \App\Model\Kategorie @inject */
	public $kategorie;

	public function renderTymy() {
		$this->template->tymy = $this->tymy->getTymy();
	}

	public function renderKategorie() {
		$this->template->kategorie = $this->kategorie->getCategories();
		$this->template->kategoriePrevod = Kategorie::$kategorie;
	}

}