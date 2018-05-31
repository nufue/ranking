<?php
declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\Exceptions\CompetitorNotFoundException;
use App\Model\Competitors;
use App\Model\CountedCompetitions;
use App\Model\Suggest;
use App\Model\TeamMembersCount;
use App\Model\TeamNameOverrides;
use App\Model\Years;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Http\IResponse;
use Nette\Utils\Html;

final class CountedCompetitionsPresenter extends BasePresenter
{
	/** @var CountedCompetitions */
	private $countedCompetitions;

	/** @var null|int */
	private $maxYearFrom = null;

	/** @var null|int */
	private $editId = null;

	/** @var array */
	private $cc = [];

	public function __construct(CountedCompetitions $countedCompetitions)
	{
		parent::__construct();
		$this->countedCompetitions = $countedCompetitions;
	}

	public function actionDefault(): void
	{
		$this->template->countedCompetitions = $this->countedCompetitions->getAll();
	}

	public function actionAdd(): void
	{
		$this->template->countedCompetitions = $cc = $this->countedCompetitions->getAll();
		foreach ($cc as $m) {
			if ($m['year_from'] !== null && ($m['year_from'] > $this->maxYearFrom || $this->maxYearFrom === null)) {
				$this->maxYearFrom = $m['year_from'];
			}
		}
		if ($this->maxYearFrom !== null) {
			$this->getComponent('addForm')->setDefaults(['year_from' => $this->maxYearFrom + 1]);
		}
	}

	public function actionEdit(string $id): void
	{
		$this->editId = (int)$id;
		$this->template->countedCompetitions = $this->cc = $this->countedCompetitions->getAll();
		$this->getComponent('addForm')->setDefaults([
			'count' => $this->cc[$this->editId]->count,
			'year_from' => $this->cc[$this->editId]->year_from,
			'year_to' => $this->cc[$this->editId]->year_to,
		]);
	}

	protected function createComponentAddForm(): Form
	{
		$form = new Form();
		$form->addInteger('count', 'Počet započítávaných závodů')
			->setDefaultValue(12)
			->addRule($form::RANGE, 'Počet započítávaných závodů musí být mezi %d a %d.', [1, 30]);
		$from = $form->addText('year_from', 'Rok počátku platnosti nové hodnoty', 4)->setType('number')->setAttribute('min', $this->maxYearFrom ?? 2018)->setAttribute('max', 2050);
		$to = $form->addText('year_to', 'Rok konce platnosti nové hodnoty', 4)->setType('number')->setAttribute('min', $this->maxYearFrom ?? 2018)->setAttribute('max', 2050);
		if ($this->editId !== null) {
			$from->setDisabled();
		}
		$form->addSubmit('save', 'Uložit');
		$form->onValidate[] = function (Form $form): void {
			$values = $form->getValues();
			if ($this->editId !== null) {
				$values->year_from = $this->cc[$this->editId]->year_from;
			} else {
				$values->year_from = $values->year_from === '' ? null : (int)$values->year_from;
			}
			$values->year_to = $values->year_to === '' ? null : (int)$values->year_to;
			if ($values->year_from !== null && $values->year_to !== null && $values->year_from > $values->year_to) {
				$form->addError('Rok konce platnosti nesmí být menší než rok počátku platnosti.');
			}
			if ($this->countedCompetitions->overlaps($values->year_from, $values->year_to, $this->editId)) {
				$form->addError('Zadané rozpětí roků se překrývá s dříve zadanými hodnotami.');
			}
		};
		$form->onSuccess[] = function (Form $form, $values): void {
			if ($this->editId !== null) {
				$values->year_from = $this->cc[$this->editId]->year_from;
			} else {
				$values->year_from = $values->year_from === '' ? null : (int)$values->year_from;
			}
			$values->year_from = $values->year_from === '' ? null : (int)$values->year_from;
			$values->year_to = $values->year_to === '' ? null : (int)$values->year_to;
			if ($this->editId !== null) {
				$this->countedCompetitions->update($this->editId, $values->year_to, (int)$values->count);
			} else {
				$this->countedCompetitions->add($values->year_from, $values->year_to, (int)$values->count);
			}
			$msg = Html::el()->addText('Pro rozmezí roků od ')
				->addHtml($this->getFormattedYear($values->year_from))
				->addText(' do ')
				->addHtml($this->getFormattedYear($values->year_to))
				->addText(' byl nastaven maximální počet členů týmu ')
				->addHtml(Html::el('b')->setText($values->count))
				->addText('.');
			$this->flashMessage($msg);
			$this->redirect('this');
		};
		return $form;
	}

	private function getFormattedYear(?int $year): Html
	{
		if ($year !== null) {
			return Html::el('b')->setText($year);
		}

		return Html::el('em')->setText('nezadáno');
	}
}
