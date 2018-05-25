<?php
declare(strict_types=1);

namespace App\FrontModule\Presenters;

use App\Exceptions\CompetitionNotFoundException;
use App\Model\Competition;
use App\Model\CompetitionCategories;
use App\Model\Competitions;
use App\Model\CompetitionTypes;
use App\Model\Competitors;
use App\Model\ScoringTables;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Http\IResponse;
use App\Forms as AF;

final class ZavodyPresenter extends BasePresenter
{

	/** @persistent int */
	public $id;

	/** @var Competition|null */
	private $record;

	/** @var \App\Model\Competitions */
	private $competitions;

	/** @var CompetitionTypes */
	private $competitionTypes;

	/** @var CompetitionCategories */
	private $competitionCategories;

	/** @var Competitors */
	private $competitors;

	/** @var ScoringTables */
	private $scoringTables;

	public function __construct(Competitions $competitions, Competitors $competitors, CompetitionTypes $competitionTypes, ScoringTables $scoringTables, CompetitionCategories $competitionCategories)
	{
		parent::__construct();
		$this->competitions = $competitions;
		$this->competitors = $competitors;
		$this->competitionTypes = $competitionTypes;
		$this->scoringTables = $scoringTables;
		$this->competitionCategories = $competitionCategories;
	}

	public function startup(): void
	{
		parent::startup();
		if (\in_array($this->getAction(), ['add', 'edit'], true) && !$this->getUser()->isInRole('admin')) {
			throw new BadRequestException('Pro přidávání nebo úpravu závodů musíte být správce.', IResponse::S403_FORBIDDEN);
		}
	}

	public function actionAdd(string $year)
	{
		$this->year = (int)$year;
		$this->template->rok = (int)$year;
	}

	public function renderDefault(string $year)
	{
		$this->template->competitions = $this->competitions->loadAllCompetitions((int)$year);
		$this->template->year = $year;
		$this->template->competitionTypes = $this->competitionTypes->getByYear((int)$year);;
	}

	public function actionEdit(string $year, string $id)
	{
		try {
			$this->record = $c = $this->competitions->getCompetition((int)$id);
		} catch (CompetitionNotFoundException $exc) {
			throw new BadRequestException('Závod nenalezen', 0, $exc);
		}
		$this->year = (int)$year;
		$this->template->rok = $c->getYear();
		$defaults = [
			'nazev' => $c->getTitle(),
			'kategorie' => $c->getCategoryId(),
			'typ' => $c->getType(),
			'datum_od' => $c->getFrom()->format('j. n. Y'),
			'datum_do' => $c->getTo()->format('j. n. Y'),
			'zobrazovat' => $c->isVisible(),
			'vysledky' => $c->hasResults(),
		];
		$this['competitionForm']->setDefaults($defaults);
	}

	public function renderDetail(string $id, string $year)
	{
		/** @var Template $template */
		$template = $this->getTemplate();
		$template->getLatte()->addFilter('rank', function ($rank, $type) {
			$scoringTable = $this->scoringTables->getByCompetitionType($type);
			return $scoringTable[(int)$rank] ?? '-';
		});
		try {
			$competition = $this->competitions->getCompetition((int)$id);
		} catch (CompetitionNotFoundException $exc) {
			throw new BadRequestException('Závod nenalezen', 0, $exc);
		}
		$this->template->competition = $competition;
		$this->template->year = $year;
		$this->template->competitors = $this->competitors->loadCompetitorsForCompetition((int)$id);
		$this->template->competitionTypes = $this->competitionTypes->getByYear((int)$year);
	}

	public function createComponentCompetitionForm(): AF\Form
	{
		$form = new AF\Form();
		$form->addText('nazev', 'Název závodu', 50)->setAttribute('autofocus')->setRequired('Vyplňte prosím název návodu');
		$form->addSelect('kategorie', 'Omezení kategorie účastníků', $this->competitionCategories->getByYearForSelect($this->year))->setPrompt('-- vyberte, je-li závod omezen na určitou kategorii --');
		$form->addSelect('typ', 'Typ závodu', $this->competitionTypes->getByYear($this->year))->setPrompt('-- vyberte typ závodu --')->setRequired('Vyberte prosím typ závodu');
		$form->addDatePicker('datum_od', 'Datum od', 50)->setRequired('Vyberte prosím datum počátku')->setAttribute('placeholder', 'd. m. yyyy');
		$form->addDatePicker('datum_do', 'Datum do', 50)->setAttribute('placeholder', 'pro jednodenní závody nemusíte vyplňovat');
		$form->addCheckbox('zobrazovat', 'Zobrazovat závod');
		$form->addCheckbox('vysledky', 'Jsou zadány výsledky');

		$form->addSubmit('send', 'Uložit změny');
		$form->onSuccess[] = function (Form $form, $values) {
			$this->competitionFormSucceeded($form, $values);
		};
		return $form;
	}

	public function competitionFormSucceeded(Form $form, $values): void
	{
		if ($this->id !== null && $this->record === null) {
			throw new BadRequestException;
		}

		if ($values->datum_do === null)
			$values->datum_do = clone $values->datum_od;
		if ($values->datum_od->format('Y-m-d') > $values->datum_do->format('Y-m-d')) {
			$form->addError('Datum začátku závodu je později než datum konce závodu.');
			return;
		}
		if ($values->kategorie === null)
			$values->kategorie = '';

		if ($this->id !== null && $this->record !== null) {
			$this->competitions->updateCompetition($this->record->getId(), $values->nazev, $values->kategorie, $values->typ, $values->datum_od, $values->datum_do, $values->zobrazovat, $values->vysledky);
			$this->flashMessage("Informace o závodu byly upraveny.", "success");
			$this->redirect("default");
		} else {
			$this->competitions->addCompetition($values->nazev, $values->kategorie, $values->typ, $values->datum_od, $values->datum_do, $values->zobrazovat, $values->vysledky);
			$this->flashMessage("Závod byl přidán.", "success");
			$this->redirect("default", ['rok' => $values->datum_od->format('Y')]);
		}
	}

}

