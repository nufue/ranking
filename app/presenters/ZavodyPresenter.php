<?php
declare(strict_types=1);

namespace App\Presenters;

use App\Exceptions\CompetitionNotFoundException;
use App\Model\Competition;
use App\Model\Competitions;
use App\Model\CompetitionTypes;
use App\Model\Competitors;
use App\Model\ScoringTables;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use App\Model\Ranking;

final class ZavodyPresenter extends BasePresenter
{

	/** @persistent int */
	public $id;

	/** @var Competition|null */
	private $record;

	/** @var \App\Model\Competitions */
	private $zavody;

	/** @var CompetitionTypes */
	private $competitionTypes;

	/** @var Competitors */
	private $competitors;

	/** @var ScoringTables */
	private $scoringTables;

	public function __construct(Competitions $competitions, Competitors $competitors, CompetitionTypes $competitionTypes, ScoringTables $scoringTables)
	{
		parent::__construct();
		$this->zavody = $competitions;
		$this->competitors = $competitors;
		$this->competitionTypes = $competitionTypes;
		$this->scoringTables = $scoringTables;
	}

	public function actionAdd(string $year) {
		$this->year = (int)$year;
		$this->template->rok = (int)$year;
	}

	public function renderDefault(string $year)
	{
		$this->template->zavody = $this->zavody->loadAllCompetitions((int)$year);
		$this->template->rok = $year;
		$this->template->typyZavodu = $this->competitionTypes->getByYear((int)$year);;
	}

	public function actionEdit(string $id)
	{
		try {
			$this->record = $c = $this->zavody->getCompetition((int)$id);
		} catch (CompetitionNotFoundException $exc) {
			throw new BadRequestException('Závod nenalezen', 0, $exc);
		}
		$this->template->rok = $c->getYear();
		$defaults = [
			'nazev' => $c->getTitle(),
			'kategorie' => $c->getCategory(),
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
		$this->getTemplate()->getLatte()->addFilter('rank', function ($rank, $type) {
			$scoringTable = $this->scoringTables->getByCompetitionType($type);
			return $scoringTable[(int)$rank] ?? '-';
		});
		try {
			$competition = $this->zavody->getCompetition((int)$id);
		} catch (CompetitionNotFoundException $exc) {
			throw new BadRequestException('Závod nenalezen', 0, $exc);
		}
		$this->template->zavod = $competition;
		if ($year === null) $year = $competition->getYear();
		$this->template->rok = $year;
		$this->template->zavodnici = $this->competitors->loadCompetitorsForCompetition((int)$id);
		$this->template->typyZavodu = $this->competitionTypes->getByYear((int)$year);
	}

	public function createComponentCompetitionForm(): Form
	{
		$form = new Form;
		$form->addText('nazev', 'Název závodu', 50)->setAttribute('autofocus')->setRequired('Vyplňte prosím název návodu');
		$form->addSelect('kategorie', 'Omezení kategorie účastníků', Ranking::$competitionCategories)->setPrompt('-- vyberte, je-li závod omezen na určitou kategorii --');
		$form->addSelect('typ', 'Typ závodu', $this->competitionTypes->getByYear($this->year))->setPrompt('-- vyberte typ závodu --')->setRequired('Vyberte prosím typ závodu');
		$form->addDatePicker('datum_od', 'Datum od', 10)->setRequired('Vyberte prosím datum počátku')->setAttribute('placeholder', 'd. m. yyyy');
		$form->addDatePicker('datum_do', 'Datum do', 10)->setAttribute('placeholder', 'pro jednodenní závody nemusíte vyplňovat');
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
		if ($this->id && $this->record === null) {
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

		if ($this->id) {
			$this->zavody->updateCompetition($this->record->getId(), $values->nazev, $values->kategorie, $values->typ, $values->datum_od, $values->datum_do, $values->zobrazovat, $values->vysledky);
			$this->flashMessage("Informace o závodu byly upraveny.", "success");
			$this->redirect("default");
		} else {
			$this->zavody->addCompetition($values->nazev, $values->kategorie, $values->typ, $values->datum_od, $values->datum_do, $values->zobrazovat, $values->vysledky);
			$this->flashMessage("Závod byl přidán.", "success");
			$this->redirect("default", ['rok' => $values->datum_od->format('Y')]);
		}
	}

}

