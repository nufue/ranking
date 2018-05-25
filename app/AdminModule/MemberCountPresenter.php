<?php
declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\Exceptions\CompetitorNotFoundException;
use App\Model\Competitors;
use App\Model\Suggest;
use App\Model\TeamMembersCount;
use App\Model\TeamNameOverrides;
use App\Model\Years;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Http\IResponse;
use Nette\Utils\Html;

final class MemberCountPresenter extends BasePresenter
{
	/** @var TeamMembersCount */
	private $teamMembersCount;

	/** @var null|int */
	private $maxYearFrom = null;

	public function __construct(TeamMembersCount $teamMembersCount)
	{
		parent::__construct();
		$this->teamMembersCount = $teamMembersCount;
	}

	public function actionDefault(): void
	{
		$this->template->memberCount = $this->teamMembersCount->getAll();
	}

	public function actionAdd(): void
	{
		$this->template->memberCount = $mc = $this->teamMembersCount->getAll();
		foreach ($mc as $m) {
			if ($m['year_from'] !== null && ($m['year_from'] > $this->maxYearFrom || $this->maxYearFrom === null)) {
				$this->maxYearFrom = $m['year_from'];
			}
		}
		if ($this->maxYearFrom !== null) {
			$this->getComponent('addForm')->setDefaults(['year_from' => $this->maxYearFrom + 1]);
		}
	}

	protected function createComponentAddForm(): Form
	{
		$form = new Form();
		$form->addInteger('count', 'Maximální počet členů týmu')
			->setDefaultValue(15)
			->addRule($form::RANGE, 'Maximální počet členů týmu musí být mezi %d a %d.', [1, 30]);
		$form->addText('year_from', 'Rok počátku platnosti nové hodnoty', 4)->setType('number')->setAttribute('min', $this->maxYearFrom ?? 2018)->setAttribute('max', 2050);
		$form->addText('year_to', 'Rok konce platnosti nové hodnoty', 4)->setType('number')->setAttribute('min', $this->maxYearFrom ?? 2018)->setAttribute('max', 2050);
		$form->addSubmit('save', 'Uložit');
		$form->onValidate[] = function (Form $form): void {
			$values = $form->getValues();
			if ($values->year_from !== '' && $values->year_to !== '' && (int)$values->year_from > (int)$values->year_to) {
				$form->addError('Rok konce platnosti nesmí být menší než rok počátku platnosti.');
			}
			if ($this->teamMembersCount->overlaps($values->year_from === '' ? null : (int)$values->year_from, $values->year_to === '' ? null : (int)$values->year_to)) {
				$form->addError('Zadané rozpětí roků se překrývá s dříve zadanými hodnotami.');
			}
		};
		$form->onSuccess[] = function (Form $form, $values): void {
			\Tracy\Debugger::dump($values);
		};
		return $form;
	}
}
