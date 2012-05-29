<?php

use \Nette\Application\UI\Form;

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class TymyPresenter extends BasePresenter {

	private $rok = 2012;

	public function renderDefault() {
		$this->template->ligy = array('1' => '1. liga', '2a' => '2. liga, sk. A', '2b' => '2. liga, sk. B', '2c' => '2. liga, sk. C');
		$this->template->rok = $this->rok;
		$this->template->tymy = $this->context->tymy->getTymyRok($this->rok);
	}

	public function renderDetail($id) {
		$detail = $this->context->tymy->getTym($id);
		$this->template->detail = $detail['info'];
		$this->template->zavodnici = $detail['zavodnici'];
	}

	public function createComponentAddForm() {
		$form = new Form;
		for ($i = 1; $i <= 15; $i++) {
			//$form->addText('zavodnik'.$i, $i, 40);

			$form->addSuggestInput('zavodnik' . $i, $i)
					->setSuggestLink($this->link('signalSuggest!'))
					->addJsOptions('itemsPerPage', 10)
					->addJsOptions('noControl', true)
					->addJsOptions('minchars', 2)
					->addJsOptions('constant', true)
					->addJsOptions('componentName', $this->getName());
		}

		return $form;
	}

	public function handleSignalSuggest($typedText = '') {
		$this->matches = $this['dibiSuggester']->getSuggestions(NULL);
		$this->terminate(new JsonResponse($this->matches));
	}

	protected function createComponentDibiSuggester() {
		$suggester = new DibiSuggester();
		return $suggester
						->setTable('zavodnici')
						->setColumn('cele_jmeno')
						->setWhere('[cele_jmeno] LIKE %s');
	}

}
