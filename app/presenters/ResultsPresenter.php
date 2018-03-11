<?php
declare(strict_types=1);

namespace App\Presenters;

use App\Exceptions\CompetitorNotFoundException;
use App\Model\Categories;
use App\Model\CheckedResultRow;
use App\Model\CheckedResults;
use App\Model\Competitions;
use App\Model\Competitors;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Rendering\DefaultFormRenderer;
use Nette\Http\IResponse;
use Nette\Http\Session;
use Nette\Http\SessionSection;

final class ResultsPresenter extends BasePresenter
{

	private static $columnTypes = [
		'registrace' => 'Registrace',
		'prijmeni' => 'Příjmení, jméno',
		'kategorie' => 'Kategorie',
		'druzstvo' => 'Družstvo',
		'cips1' => '1. závod CIPS',
		'umisteni1' => '1. závod umístění',
		'cips2' => '2. závod CIPS',
		'umisteni2' => '2. závod umístění',
	];
	private $pocetSloupcu = 0;
	private $pocetRadku = 0;

	/** @var Competitions */
	private $zavody;

	/** @var Competitors */
	private $competitors;

	/** @var CheckedResults */
	private $checkedResults;

	/** @var Categories */
	private $categories;

	/** @var array */
	private $problemRegistrations = [];

	/** @var array */
	private $problemCategories = [];

	/** @var CheckedResultRow[] */
	private $results = [];

	/** @var int */
	private $competitionYear = 0;

	/** @var int */
	private $competitionId = -1;

	public function __construct(Competitions $competitions, Competitors $competitors, CheckedResults $checkedResults, Categories $categories)
	{
		parent::__construct();
		$this->zavody = $competitions;
		$this->competitors = $competitors;
		$this->checkedResults = $checkedResults;
		$this->categories = $categories;
	}

	public function startup(): void
	{
		parent::startup();
		if (!$this->getUser()->isInRole('admin')) {
			throw new BadRequestException('Pro přidávání výsledků závodů musíte být správce.', IResponse::S403_FORBIDDEN);
		}
	}

	public function actionAdd(string $id): void
	{
		$this->template->c = $this->zavody->getCompetition((int)$id);
		$this->template->id = $this->competitionId = (int)$id;
	}

	protected function createComponentImportForm(): Form
	{
		$form = new Form;
		$form->addTextArea('vysledky', 'Výsledky', 80, 25)
			->setAttribute('placeholder', 'Sem vložte zkopírovaná data z Excelových výsledků - obvykle z listu "Výsledková listina" - včetně řádku záhlaví tabulky (REG, příjmení jméno, kat, ...)')
			->setRequired('Vyplňte prosím pole s výsledky');
		$form->addSubmit('send', 'Zkontrolovat výsledky');
		$form->onSuccess[] = function (Form $form, $values) {
			$this->importFormSucceeded($form, $values);
		};
		$form->setRenderer(new DefaultFormRenderer());
		return $form;
	}

	private function getResultsSection(int $competitionId): SessionSection
	{
		/** @var Session $session */
		$session = $this->getSession();
		return $session->getSection('results_' . $competitionId);
	}

	public function importFormSucceeded(Form $form, $values): void
	{
		$section = $this->getResultsSection($this->competitionId);
		$section->data = trim($values->vysledky);
		$this->redirect('columns', $this->competitionId);
	}

	public function actionColumns(string $id): void
	{
		$this->template->c = $this->zavody->getCompetition((int)$id);
		$this->template->id = $this->competitionId = (int)$id;
		$section = $this->getResultsSection($this->competitionId);

		$lines = explode("\n", $section->data);
		$columnCount = null;

		$table = [];

		foreach ($lines as $line) {
			$cols = explode("\t", $line);
			$columns = 0;
			$row = [];
			foreach ($cols as $col) {
				$col = trim($col);
				$row[] = $col;
				$columns++;
			}
			$table[] = $row;
			if ($columnCount === null || $columns > $columnCount) {
				$columnCount = $columns;
			}
		}

		$this->pocetSloupcu = $columnCount;
		$this->template->pocetSloupcu = $columnCount;
		$this->pocetRadku = \count($table);
		$this->template->pocetRadku = \count($table);

		$cipsFound = 0;
		$umisteniFound = 0;
		$defaults = [];
		for ($i = 0; $i < $columnCount; $i++) {
			$candidate = '';

			foreach ($table as $row) {
				if (isset($row[$i])) {
					$row[$i] = mb_strtolower($row[$i]);
					if ($row[$i] === 'reg') {
						$candidate = 'registrace';
						break;
					}
					if ($row[$i] === 'příjmení, jméno' || $row[$i] === 'příjmení jméno') {
						$candidate = 'prijmeni';
						break;
					}
					if ($row[$i] === 'kat' || $row[$i] === 'kat.') {
						$candidate = 'kategorie';
						break;
					}
					if ($row[$i] === 'družstvo' || $row[$i] === 'organizace') {
						$candidate = 'druzstvo';
						break;
					}
					if ($row[$i] === 'cips') {
						if ($cipsFound === 0) {
							$candidate = 'cips1';
							$cipsFound++;
							break;
						}
						if ($cipsFound === 1) {
							$candidate = 'cips2';
							$cipsFound++;
							break;
						}
					}
					if ($row[$i] === 'um.' || $row[$i] === 'poř.') {
						if ($umisteniFound === 0) {
							$candidate = 'umisteni1';
							$umisteniFound++;
							break;
						}
						if ($umisteniFound === 1) {
							$candidate = 'umisteni2';
							$umisteniFound++;
							break;
						}
					}
				}
			}
			if ($candidate !== '') {
				$defaults['sloupec' . $i] = $candidate;
			}
		}
		$this->template->tabulka = $table;
		$this->getResultsSection($this->competitionId)->table = $table;
		$this['selectColumnsForm']->setDefaults($defaults);
	}

	public function createComponentSelectColumnsForm(): Form
	{
		$form = new Form();
		for ($i = 0; $i < $this->pocetSloupcu; $i++) {
			$form->addSelect('sloupec' . $i, '', self::$columnTypes)->setPrompt('-');
		}

		$form->addRadioList('prvni_radek', '', range(0, $this->pocetRadku))->setRequired('Označte v prvním sloupci řádek, na kterém začínají výsledky.');

		$form->addSubmit('send', 'Přidat výsledky');
		$form->onSuccess[] = function (Form $form, $values) {
			$this->selectColumnsFormSucceeded($form, $values);
		};
		return $form;
	}

	public function selectColumnsFormSucceeded(Form $form, $values): void
	{
		$table = $this->getResultsSection($this->competitionId)->table;
		$columns = [];
		foreach ($values as $k => $v) {
			if (mb_strpos($k, 'sloupec') === 0) {
				$columnIndex = (int)str_replace('sloupec', '', $k);
				if ($v !== null && trim($v) !== '')
					$columns[$v] = $columnIndex;
			}
		}
		if (!isset($columns['prijmeni'])) {
			$form->addError('Nebyl vybrán sloupec obsahující jméno závodníka.');
		}
		if (!isset($columns['registrace'])) {
			$form->addError('Nebyl vybrán sloupec obsahující číslo registrace.');
		}
		if (!isset($columns['druzstvo'])) {
			$form->addError('Nebyl vybrán sloupec obsahující název družstva.');
		}
		if (!isset($columns['kategorie'])) {
			$form->addError('Nebyl vybrán sloupec obsahující označení kategorie.');
		}
		if (!isset($columns['cips1'])) {
			$form->addError('Nebyl vybrán ani jeden sloupec obsahující CIPS body.');
		}
		if (!isset($columns['umisteni1'])) {
			$form->addError('Nebyl vybrán ani jeden sloupec obsahující umístění v závodě.');
		}
		if ($form->hasErrors()) {
			return;
		}
		$firstRowIndex = $values->prvni_radek;
		$radku = 0;
		$id = 0;
		$vysledky = [];
		foreach ($table as $row) {
			if ($radku++ < $firstRowIndex)
				continue;
			if (!isset($row[$columns['prijmeni']]) || trim($row[$columns['prijmeni']]) === '')
				continue;
			$prijmeni = trim($row[$columns['prijmeni']]);
			$registrace = $this->trimUnicode($row[$columns['registrace']]);
			$kategorie = trim($row[$columns['kategorie']]);
			$tym = trim($row[$columns['druzstvo']]);

			$cips1 = trim($row[$columns['cips1']]);
			$umisteni1 = trim($row[$columns['umisteni1']]);

			$cips2 = isset($columns['cips2']) ? trim($row[$columns['cips2']]) : '';
			$umisteni2 = isset($columns['umisteni2']) ? trim($row[$columns['umisteni2']]) : '';

			$vysledky[$id] = ['prijmeni' => $prijmeni, 'registrace' => $registrace, 'kategorie' => $kategorie, 'tym' => $tym, 'cips1' => $cips1, 'umisteni1' => $umisteni1, 'cips2' => $cips2, 'umisteni2' => $umisteni2];
			$id++;
		}
		$this->getResultsSection($this->competitionId)->parsedResults = $vysledky;
		$this->redirect('review', $this->competitionId);
	}

	public function actionReview(string $id): void
	{
		$this->template->c = $c = $this->zavody->getCompetition((int)$id);
		$this->competitionYear = $c->getYear();
		$this->template->id = $this->competitionId = (int)$id;
		$section = $this->getResultsSection($this->competitionId);
		$allowedCategories = $this->categories->getCompetitorCategoriesByYear($c->getYear());
		$checkedResults = $this->checkedResults->check($section->parsedResults, $c->getYear(), $allowedCategories);
		$this->problemRegistrations = [];
		$this->problemCategories = [];
		$defaults = [];
		foreach ($checkedResults as $rowId => $cr) {
			if ($cr->getStatus()->isProblem()) {
				$this->problemCategories[$rowId] = 'category';
			}
			if ($cr->getStatus()->isNameDifferent()) {
				$this->problemRegistrations[$rowId] = 'name';
				$defaults['reg_' . $rowId] = $cr->getRegistration();
			}
		}
		$this->template->problemRegistrations = $this->problemRegistrations;
		$this->template->problemCategories = $this->problemCategories;
		$this->template->results = $this->results = $checkedResults;
		$this['confirmResultsForm']->setDefaults($defaults);
	}

	public function createComponentConfirmResultsForm(): Form
	{
		$form = new Form();
		foreach ($this->problemRegistrations as $rowId => $v) {
			$form->addText('reg_' . $rowId, '');
		}
		if (\count($this->problemCategories) > 0) {
			foreach ($this->problemCategories as $rowId => $v) {
				$form->addSelect('cat_' . $rowId, '', $this->categories->getCompetitorCategoriesByYear($this->competitionYear))->setPrompt('-- zvolte kategorii --')->setRequired('Prosím vyberte kategorii.');
			}
		} else {
			$form->addSubmit('save', 'Uložit výsledky');
		}
		$form->addSubmit('fix', 'Znovu zkontrolovat');
		$form->onSuccess[] = function (Form $form, $values) {
			$this->confirmResultsFormSucceeded($form, $values);
		};
		return $form;
	}

	public function confirmResultsFormSucceeded(Form $form, $values): void
	{
		$section = $this->getResultsSection($this->competitionId);
		$results = $section->parsedResults;
		$btn = $form->isSubmitted();
		$action = ($btn instanceof SubmitButton) ? $btn->getName() : '';


		if ($action === 'fix') {
			$needsRedirect = false;
			foreach ($values as $k => $v) {
				if ($v !== null && $v !== '' & preg_match('~^cat_(\d+)$~', $k, $m)) {
					$results[$m[1]]['kategorie'] = $v;
					$needsRedirect = true;
				}
				if ($v !== null && $v !== '' && preg_match('~^reg_(\d+)$~', $k, $m)) {
					$results[$m[1]]['registrace'] = $v;
					$needsRedirect = true;
				}
			}
			if ($needsRedirect) {
				$section->parsedResults = $results;
				$this->redirect('this', $this->competitionId);
			}

		} else if ($action === 'save') {
			$this->zavody->deleteResults($this->competitionId);

			// všichni závodníci mají kategorii zadanou nebo historicky v databázi

			$countSuccess = 0;
			\Tracy\Debugger::barDump($results);
			/** @var CheckedResultRow $v */
			foreach ($this->results as $v) {
				if ($v->getStatus()->isNew()) {
					if (preg_match('~^\d+$~', $v->getRegistration())) {
						$competitorId = $this->competitors->addNewRegisteredCompetitor($v->getRegistration(), $v->getFullName());
					} else {
						$competitorId = $this->competitors->addNewUnregisteredCompetitor($v->getFullName());
					}
					if ($v->getCategory() !== null) {
						$this->categories->addCompetitorToCategory($competitorId, $v->getCategory(), $this->competitionYear);
					} else {
						$form->addError('K závodníkovi '.$v->getFullName().' se nepodařilo najít platnou kategorii.');
						return;
					}
				} else {
					try {
						if (preg_match('~^\d+$~', $v->getRegistration())) {
							$competitorId = $this->competitors->getIdByRegistration($v->getRegistration());
						} else {
							$competitorId = $this->competitors->getUnregisteredIdByName($v->getFullName());
						}
					} catch (CompetitorNotFoundException $exc) {
						$form->addError('Nepodařilo se najít existujícího závodníka se jménem ' . $v->getFullName() . ' a registrací ' . $v->getRegistration());
						return;
					}
				}
				if ($v->getStatus()->isAddCategory()) {
					if ($v->getCategory() !== null) {
						$this->categories->addCompetitorToCategory($competitorId, $v->getCategory(), $this->competitionYear);
					} else {
						$form->addError('Nepodařilo se najít platnou kategorii k závodníkovi s ID = '.$competitorId);
						return;
					}
				}

				$cips1 = $v->hasRound(1) ? $v->getRound(1)->getCips() : null;
				$umisteni1 = $v->hasRound(1) ? $v->getRound(1)->getRank() : null;
				$cips2 = $v->hasRound(2) ? $v->getRound(2)->getCips() : null;
				$umisteni2 = $v->hasRound(2) ? $v->getRound(2)->getRank() : null;

				$this->zavody->addVysledek($this->competitionId, $competitorId, $v->getTeam(), $cips1, $umisteni1, $cips2, $umisteni2);
				$countSuccess++;
			}
			if ($countSuccess > 0) {
				$this->flashMessage('Do závodu bylo přidáno ' . $countSuccess . ' závodníků.');
				$this->zavody->markResultsPresent($this->competitionId);
				$this->redirect('Zavody:detail', $this->competitionId);
			} else {
				$this->flashMessage('Do závodu se nepodařilo přidat žádného závodníka.');
				$this->redirect('Zavody:detail', $this->competitionId);
			}
		}
	}

	private function trimUnicode(string $input): string
	{
		return preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $input);
	}
}

