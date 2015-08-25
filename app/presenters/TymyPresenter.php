<?php

namespace App\Presenters;

use Nette\Application\UI\Form, App\Model\Tymy, App\Model\Kategorie, Nette\Application\Responses;

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class TymyPresenter extends BasePresenter {

	/** @var \App\Model\Tymy @inject */
	public $tymy;
	
	/** @var \App\Model\Zavodnici @inject */
	public $zavodnici;
	
	/** @var \App\Model\Suggest @inject */
	public $suggest;
	
	public function renderDefault($rok = NULL) {
		if ($rok === NULL) $rok = self::$defaultYear;
		$this->template->ligy = Tymy::$ligy;
		$this->template->rok = $rok;
		$this->template->tymy = $this->tymy->getTymyRok($rok);
	}

	public function renderDetail($id, $rok) {
		$this->template->ligy = Tymy::$ligy;
		$detail = $this->tymy->getTym($id);
		$this->template->kategoriePrevod = Kategorie::$kategorie;
		$this->template->detail = $detail['info'];
		$this->template->zavodnici = $detail['zavodnici'];
		$index = 1;
		$defaults = [];
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
		$form->onSuccess[] = $this->addFormSubmitted;
		return $form;
	}

	public function addFormSubmitted(Form $form, $values) {
		$id = $values['id'];
		if (!empty($id)) {
			$this->tymy->removeZavodnici($id);
			foreach ($values as $k => $v) {
				if (mb_substr($k, 0, 8) != 'zavodnik') {
					continue;
				}
				if (trim($v) == '')
					continue;
				if (preg_match('~^\d+$~', $v)) {
					$zavodnik = $this->zavodnici->getZavodnikByRegistrace($v);
				} else {
					$zavodnik = $this->zavodnici->getZavodnikByJmeno($v);
				}
				if ($zavodnik !== NULL) {
					$this->tymy->addZavodnik($id, $zavodnik->id);
					$this->flashMessage('Závodník ' . $v . ' byl přidán do týmu ID = ' . $id);
				} else {
					$this->flashMessage('Závodníka ' . $v . ' se nepodařilo vyhledat.', 'error');
				}
			}
			$this->redirect('detail', $id);
		}
	}

	public function actionSuggest($typedText) {
		$response = $this->suggest->getSuggest($typedText);
		$this->sendResponse(new Nette\Application\Responses\JsonResponse(['values' => $response]));
	}

}
