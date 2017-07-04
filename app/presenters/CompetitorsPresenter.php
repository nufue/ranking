<?php

namespace App\Presenters;

use Nette\Application\UI\Form;

final class CompetitorsPresenter extends BasePresenter {

	/** @var \App\Model\Competitors @inject */
	public $competitors;

	public function renderDefault() {
		$this->template->rok = $this->defaultYear->getDefaultYear();
	}

	public function renderResults($term) {
		$this->template->results = $this->competitors->search($term);
		$this->template->rok = $this->defaultYear->getDefaultYear();
	}

	public function createComponentSearchForm() {
		$form = new Form;
		$form->addText('search', 'Jméno nebo číslo registrace')->setRequired('Zadejte jméno nebo číslo registrace');
		$form->addSubmit('send', 'Hledat');
		$form->onSuccess[] = [$this, 'searchFormSuccess'];
		return $form;
	}

	public function searchFormSuccess(Form $form, $values) {
		$this->redirect('results', $values->search);
	}
}
