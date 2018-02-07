<?php

namespace App\Presenters;

use App\Model\Competitors;
use App\Model\Leagues;
use App\Model\Suggest;
use App\Model\Zavodnici;
use Nette\Application\UI\Form, App\Model\Teams, App\Model\Kategorie, Nette\Application\Responses;

final class TymyPresenter extends BasePresenter {

	/** @var \App\Model\Teams */
	private $tymy;
	
	/** @var \App\Model\Zavodnici */
	private $zavodnici;
	
	/** @var \App\Model\Suggest */
	private $suggest;

	/** @var \App\Model\Leagues */
	private $leagues;

	/** @var \App\Model\Competitors */
	private $competitors;

	/** @var int */
	private $year;

	public function __construct(Teams $teams, Zavodnici $zavodnici, Suggest $suggest, Leagues $leagues, Competitors $competitors)
	{
		parent::__construct();
		$this->tymy = $teams;
		$this->zavodnici = $zavodnici;
		$this->suggest = $suggest;
		$this->leagues = $leagues;
		$this->competitors = $competitors;
	}
	
	public function actionDefault($rok = NULL) {
		if ($rok === NULL) $rok = $this->defaultYear->getDefaultYear();
		$this->year = (int)$rok;
		$this->template->ligy = $this->leagues->getLeagues();
		$this->template->rok = $rok;
		$this->template->tymy = $this->tymy->loadTeamsByYear($rok);
	}

	public function actionDetail($id) {
		$this->template->ligy = $this->leagues->getLeagues();
		$detail = $this->tymy->getById($id);
		$this->template->rok = $detail['info']->rok;
		$this->year = (int)($detail['info']->rok);
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
		for ($i = 1; $i <= 13; $i++) {
			$form->addText('zavodnik' . $i, $i, 40)->getControlPrototype()->addClass('naseptavac');
		}

		$form->addHidden('id');
		$form->addSubmit('send', 'Uložit závodníky');
		$form->onSuccess[] = [$this, 'addFormSubmitted'];
		return $form;
	}

	public function addFormSubmitted(Form $form, $values) {
		$id = $values['id'];
		if (!empty($id)) {
			$this->tymy->removeAllMemberFromTeam($id);
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
					$this->tymy->addTeamMember($id, $zavodnik->id);
					$category = $this->competitors->getCompetitorCategory($this->year, (int)$zavodnik->registrace);
					$this->competitors->setCompetitorCategory($this->year, (int)$zavodnik->registrace, $category);

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
		$this->sendResponse(new Responses\JsonResponse(['values' => $response]));
	}

}
