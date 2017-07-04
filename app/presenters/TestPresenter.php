<?php

namespace App\Presenters;

final class TestPresenter extends BasePresenter {

	/** @var \App\Model\Zavodnici @inject */
	public $zavodnici;

	/** @var \App\Model\Competitors @inject */
	public $competitors;

	public function renderDefault() {

		$categories = [];

		$idCompetitors = $this->zavodnici->loadCompetitorsWithoutCategory();

		foreach ($idCompetitors as $id) {
			$registration = $this->competitors->getById($id);
			$category = \App\Model\Category::determine(2017, $registration->getBirthYear(), $registration->getGender());
			$categories[$id] = ['registration' => $registration, 'category' => $category];
		}
		$this->getTemplate()->competitors = $categories;
	}

	public function renderAll() {
		set_time_limit(0);
		$categories = [];
		$competitors = $this->zavodnici->loadAllCompetitors();
		foreach ($competitors as $c) {
			$registration = $this->competitors->getById($c->id_zavodnika);
			$category = \App\Model\Category::determine(2017, $registration->getBirthYear(), $registration->getGender());
			try {
				$original = \App\Model\Category::fromString($c->kategorie);
			} catch (\LogicException $exc) {
				$original = '';
			}
			$categories[$c->id_zavodnika] = ['registration' => $registration, 'category' => $category, 'original' => $original];
		}

		$this->getTemplate()->competitors = $categories;

	}

}