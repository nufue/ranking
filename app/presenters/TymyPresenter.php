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
		$this->template->ligy = Tymy::$ligy;
		$this->template->rok = $this->rok;
		$this->template->tymy = $this->context->tymy->getTymyRok($this->rok);
	}

	public function renderDetail($id) {
		$this->template->ligy = Tymy::$ligy;
		$detail = $this->context->tymy->getTym($id);
		$this->template->kategoriePrevod = Kategorie::$kategorie;
		$this->template->detail = $detail['info'];
		$this->template->zavodnici = $detail['zavodnici'];
		$index = 1;
		$defaults = array();
		$defaults['id'] = $id;
		foreach ($detail['zavodnici'] as $z) {
			$defaults['zavodnik' . $index] = $z->cele_jmeno;
			$index++;
		}
		$this['addForm']->setDefaults($defaults);
	}

	public function createComponentAddForm() {
		$form = new Form;
		for ($i = 1; $i <= 15; $i++) {
			$form->addText('zavodnik' . $i, $i, 40)->getControlPrototype()->addClass('naseptavac');
		}

		$form->addHidden('id');
		$form->addSubmit('send', 'Uložit závodníky');
		$form->onSuccess[] = callback($this, 'addFormSubmitted');
		return $form;
	}

	public function addFormSubmitted(Form $form) {
		$values = $form->getValues();
		$id = $values['id'];
		if (!empty($id)) {
			$this->context->tymy->removeZavodnici($id);
			foreach ($values as $k => $v) {
				if (mb_substr($k, 0, 8) != 'zavodnik') {
					continue;
				}
				if (trim($v) == '')
					continue;
				if (preg_match('~^\d+$~', $v)) {
					$zavodnik = $this->context->zavodnici->getZavodnikByRegistrace($v);
				} else {
					$zavodnik = $this->context->zavodnici->getZavodnikByJmeno($v);
				}
				if ($zavodnik !== NULL) {
					$this->context->tymy->addZavodnik($id, $zavodnik->id);
					$this->flashMessage('Závodník ' . $v . ' byl přidán do týmu ID = ' . $id);
				} else {
					$this->flashMessage('Závodníka ' . $v . ' se nepodařilo vyhledat.', 'error');
				}
			}
			$this->redirect('detail', $id);
		}
	}

	public function actionSuggest($typedText) {
		//$this->sendResponse(new Nette\Application\Responses\JsonResponse(array('test' => $typedText)));
		$response = $this->context->suggest->getSuggest($typedText);
		$this->sendResponse(new Nette\Application\Responses\JsonResponse(array('values' => $response)));
	}

}
