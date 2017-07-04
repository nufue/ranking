<?php

namespace App\Presenters;

use App\Model\Utils;
use Nette\Application\BadRequestException;
use \Nette\Application\UI\Form, \App\Model\Ranking, \App\Model\Kategorie;

final class ZavodyPresenter extends BasePresenter {

	private static $typySloupcu = array(
		'registrace' => 'Registrace',
		'prijmeni' => 'Příjmení, jméno',
		'kategorie' => 'Kategorie',
		'druzstvo' => 'Družstvo',
		'cips1' => '1. závod CIPS',
		'umisteni1' => '1. závod umístění',
		'cips2' => '2. závod CIPS',
		'umisteni2' => '2. závod umístění',
		'pocet_zavodu' => 'Počet závodů',
		'cips_celkem' => 'CIPS celkem',
		'body_celkem' => 'Součet umístění',
		'umisteni_celkem' => 'Celkové umístění',
	);
	private $pocetSloupcu = 0;
	private $pocetRadku = 0;

	/** @persistent int */
	public $id;

	/** @var object */
	private $record;

	/** @var object */
	private $vysledky;
	
	/** @var \App\Model\Competitions @inject */
	public $zavody;
	
	/** @var \App\Model\Zavodnici @inject */
	public $zavodnici;

	/** @var \App\Model\Kategorie @inject */
	public $kategorie;

	public function startup() {
		parent::startup();
		$this->getTemplate()->getLatte()->addFilter('umisteni', function($umisteni, $typZavodu) {
					$umisteni = (int) $umisteni;
					$bodovaciTabulka = Ranking::$scoringTables[Ranking::$competitionScoringType[$typZavodu]];
					if (isset($bodovaciTabulka[$umisteni])) {
						return $bodovaciTabulka[$umisteni];
					} else {
						return '-';
					}
				});

		if ($this->getAction() != 'edit' && $this->getAction() != 'detail' && $this->getAction() != 'pridatVysledky3' && $this->getAction() != 'pridatVysledky' && $this->getAction() != 'pridatVysledky2' && $this->id !== NULL) {
			$this->id = NULL;
		}
	}

	public function renderAdd($rok = NULL) {
		$this->template->rok = $rok;
	}

	public function renderDefault($rok = NULL) {
		if ($rok === NULL) $rok = $this->defaultYear->getDefaultYear();
		$this->template->zavody = $this->zavody->loadAllCompetitions($rok);
		$this->template->rok = $rok;
		$this->template->typyZavodu = Ranking::$competitionTypes;
	}

	public function actionEdit($id) {
		$this->record = $this->zavody->getCompetition($id);
		if ($this->record === FALSE) {
			throw new BadRequestException;
		}
		$this->template->rok = $this->record->rok;
		$this->record->datum_od = $this->record->datum_od->format('j. n. Y');
		$this->record->datum_do = $this->record->datum_do->format('j. n. Y');
		$this->record->zobrazovat = ($this->record->zobrazovat === 'ano');
		$this->record->vysledky = ($this->record->vysledky === 'ano');
		$this['zavodForm']->setDefaults($this->record);
	}

	public function renderDetail($id, $rok = NULL) {
		$this->template->zavod = $this->zavody->getCompetition($id);
		if ($rok === NULL) $rok = $this->template->zavod->rok;
		$this->template->rok = $rok;
		$this->template->zavodnici = $this->zavodnici->getZavodnici($id);
		$this->template->typyZavodu = Ranking::$competitionTypes;
		$this->template->kategoriePrevod = Kategorie::$kategorie;
	}

	public function createComponentZavodForm() {
		$form = new Form;
		$form->addText('nazev', 'Název závodu', 50);
		$form->addText('kategorie', 'Věková kategorie závodu', 30);
		$form->addSelect('typ', 'Typ závodu', Ranking::$competitionTypes);
		$form->addText('datum_od', 'Datum od', 10);
		$form->addText('datum_do', 'Datum do', 10);
		$form->addCheckbox('zobrazovat', 'Zobrazovat závod');
		$form->addCheckbox('vysledky', 'Jsou zadány výsledky');

		$form->addSubmit('send', 'Uložit změny');
		$form->onSuccess[] = [$this, 'zavodFormSubmitted'];
		return $form;
	}

	public function zavodFormSubmitted(Form $form, $values) {
		if ($this->id && !$this->record) {
			throw new BadRequestException;
		}

		$values['datum_od'] = Utils::convertDate($values['datum_od']);
		$values['datum_do'] = Utils::convertDate($values['datum_do']);
		$values['zobrazovat'] = $values['zobrazovat'] ? 'ano' : 'ne';
		$values['vysledky'] = $values['vysledky'] ? 'ano' : 'ne';
		if ($this->id) {
			$this->zavody->updateCompetition($this->record->id, $values);
			$this->flashMessage("Informace o závodu byly upraveny.", "success");
			$this->redirect("default");
		} else {
			$this->zavody->addCompetition($values);
			$this->flashMessage("Závod byl přidán.", "success");
			$this->redirect("default", array('rok' => $values['datum_od']->format('Y')));
		}
	}

	public function actionPridatVysledky($id) {
		$this->template->id = $id;
		$session = $this->getSession();
		if ($session->exists()) {
			$session = $session->start();
		}
	}

	public function createComponentVysledkyForm() {
		$form = new Form;
		$form->addTextArea('vysledky', 'Výsledky', 80, 25)
				->addRule(Form::FILLED, 'Vyplňte prosím pole s výsledky');
		$form->addSubmit('send', 'Odeslat');
		$form->onSuccess[] = [$this, 'vysledkyFormSubmitted'];
		return $form;
	}

	public function vysledkyFormSubmitted(Form $form, $values) {
		$vysledky = $values['vysledky'];
		$section = $this->getSession('vysledky');
		$section->vysledky = $vysledky;
		$this->redirect('pridatVysledky2', $this->id);
	}

	public function actionPridatVysledky2($id) {
		$this->template->id = $id;
		$section = $this->getSession('vysledky');
		$this->vysledky = $section->vysledky;

		$lines = explode("\n", $this->vysledky);
		$pocetSloupcu = NULL;

		$tabulka = [];
		$radky = 0;

		foreach ($lines as $line) {
			$cols = explode("\t", $line);
			$sloupcu = 0;
			$radek = [];
			$radky++;
			foreach ($cols as $col) {
				$col = trim($col);
				$radek[] = $col;
				$sloupcu++;
			}
			$tabulka[] = $radek;
			if ($pocetSloupcu == NULL)
				$pocetSloupcu = $sloupcu;
			if ($sloupcu > $pocetSloupcu)
				$pocetSloupcu = $sloupcu;
		}

		$this->pocetSloupcu = $pocetSloupcu;
		$this->template->pocetSloupcu = $pocetSloupcu;
		$this->pocetRadku = count($tabulka);
		$this->template->pocetRadku = count($tabulka);

		$cipsFound = 0;
		$umisteniFound = 0;
		$defaults = array();
		for ($i = 0; $i < $pocetSloupcu; $i++) {
			$pravdepodobnyTyp = '';

			foreach ($tabulka as $radek) {
				if (isset($radek[$i])) {
					if (mb_strtolower($radek[$i]) == 'reg') {
						$pravdepodobnyTyp = 'registrace';
						break;
					}
					if ($radek[$i] == 'Příjmení, jméno' || $radek[$i] == 'Příjmení jméno' || $radek[$i] == 'Příjmení, Jméno') {
						$pravdepodobnyTyp = 'prijmeni';
						break;
					}
					if (mb_strtolower($radek[$i]) == 'kat' || mb_strtolower($radek[$i]) == 'kat.') {
						$pravdepodobnyTyp = 'kategorie';
						break;
					}
					if ($radek[$i] == 'Družstvo' || $radek[$i] == 'Organizace') {
						$pravdepodobnyTyp = 'druzstvo';
						break;
					}
					if ($radek[$i] == 'CIPS') {
						if ($cipsFound == 0) {
							$pravdepodobnyTyp = 'cips1';
							$cipsFound++;
							break;
						}
						if ($cipsFound == 1) {
							$pravdepodobnyTyp = 'cips2';
							$cipsFound++;
							break;
						}
						if ($cipsFound == 2) {
							$pravdepodobnyTyp = 'cips_celkem';
							$cipsFound++;
							break;
						}
					}
					if ($radek[$i] == 'um.' || $radek[$i] == 'Poř.') {
						if ($umisteniFound == 0) {
							$pravdepodobnyTyp = 'umisteni1';
							$umisteniFound++;
							break;
						}
						if ($umisteniFound == 1) {
							$pravdepodobnyTyp = 'umisteni2';
							$umisteniFound++;
							break;
						}
						if ($umisteniFound == 2) {
							$pravdepodobnyTyp = 'umisteni_celkem';
							$umisteniFound++;
							break;
						}
					}
					if (mb_strtolower($radek[$i]) == 'body') {
						$pravdepodobnyTyp = 'body_celkem';
						break;
					}
				}
			}
			if ($pravdepodobnyTyp !== '') {
				$defaults['sloupec' . $i] = $pravdepodobnyTyp;
			}
		}
		$this->template->tabulka = $tabulka;
		$this->getSession('vysledky')->tabulka = $tabulka;

		$this['vysledkyParseForm']->setDefaults($defaults);
	}

	public function createComponentVysledkyParseForm() {
		$form = new Form;

		for ($i = 0; $i < $this->pocetSloupcu; $i++) {
			$form->addSelect('sloupec' . $i, '', self::$typySloupcu)->setPrompt('-');
		}

		$form->addRadioList('prvni_radek', '', range(0, $this->pocetRadku));

		$form->addSubmit('send', 'Přidat výsledky');
		$form->onSuccess[] = [$this, 'vysledkyParseFormSubmitted'];
		return $form;
	}

	public function vysledkyParseFormSubmitted(Form $form, $values) {
		$tabulka = $this->getSession('vysledky')->tabulka;
		$sloupce = [];
		foreach ($values as $k => $v) {
			if (mb_substr($k, 0, 7) == 'sloupec') {
				$cisloSloupce = (int) str_replace('sloupec', '', $k);
				if (!empty($v))
					$sloupce[$v] = $cisloSloupce;
			}
		}
		$rok = $this->zavody->getRokZavodu($this->id);
		$prvniRadek = $values['prvni_radek'];
		$radku = 0;
		$vysledky = [];
		foreach ($tabulka as $radek) {
			if ($radku++ < $prvniRadek)
				continue;
			if (!isset($radek[$sloupce['prijmeni']]))
				continue;
			$prijmeni = trim($radek[$sloupce['prijmeni']]);

			$registrace = $this->trimUnicode($radek[$sloupce['registrace']]);
			$kategorie = trim($radek[$sloupce['kategorie']]);
			if ($prijmeni == '')
				continue;

			$poznamka = '';
			$prijmeniZebricek = '';
			$kategorieDb = NULL;
			if (!preg_match('~^\d+$~', $registrace)) {
				$zavodnik = $this->zavodnici->isExistingUnregistered($prijmeni);
				if ($zavodnik === FALSE) $poznamka = 'n';
				else {
					$kategorieDb = $this->zavodnici->getUnregisteredCategory($zavodnik, $rok);
					$poznamka = 's';
				}
			} else {
				$zavodnik = $this->zavodnici->getZavodnik($registrace, $rok);
				if ($zavodnik === NULL) {
					$poznamka = 'p';
				} else {
					$kategorieDb = $zavodnik->kategorie;
					$fullName = $this->trimUnicode(str_replace('dr.', '', str_replace('ml.', '', str_replace('ing.', '', mb_strtolower(str_replace('  ', ' ', $zavodnik->cele_jmeno))))));
					$fullNameResults = $this->trimUnicode(str_replace('dr.', '', str_replace('ml.', '', str_replace('ing.', '', mb_strtolower(str_replace('  ', ' ', $prijmeni))))));

					$eFullName = preg_split('~\s+~', $fullName);
					$eFullNameResults = preg_split('~\s+~', $fullNameResults);

					$diff = array_diff($eFullNameResults, $eFullName);
					if (count($diff) > 0) {
						$poznamka = 'r';
						$prijmeniZebricek = $zavodnik->cele_jmeno;

					}
				}
			}

			$tym = $radek[$sloupce['druzstvo']];

			$cips1 = trim($radek[$sloupce['cips1']]);
			$umisteni1 = trim($radek[$sloupce['umisteni1']]);

			if (!isset($sloupce['cips2'])) { $cips2 = NULL; } else { $cips2 = trim($radek[$sloupce['cips2']]); }
			if (!isset($sloupce['umisteni2'])) { $umisteni2 = NULL; } else { $umisteni2 = trim($radek[$sloupce['umisteni2']]); }

			if ($cips1 === '')
				$cips1 = NULL;
			if ($cips2 === '')
				$cips2 = NULL;
			if ($umisteni1 === '')
				$umisteni1 = NULL;
			if ($umisteni2 === '')
				$umisteni2 = NULL;

			if ($umisteni1 !== NULL) {
				$umisteni1 = str_replace(',', '.', $umisteni1);
			}
			if ($umisteni2 !== NULL) {
				$umisteni2 = str_replace(',', '.', $umisteni2);
			}

			$vysledky[] = ['prijmeni' => $prijmeni, 'prijmeni_zebricek' => $prijmeniZebricek, 'registrace' => $registrace, 'kategorie' => $kategorie, 'tym' => $tym, 'cips1' => $cips1, 'umisteni1' => $umisteni1, 'cips2' => $cips2, 'umisteni2' => $umisteni2, 'poznamka' => $poznamka, 'kategorieDb' => $kategorieDb];
		}
		$this->getSession('vysledky')->vysledkyParsed = $vysledky;
		$this->redirect('pridatVysledky3', $this->id);
	}

	public function actionPridatVysledky3($id) {
		$this->template->id = $id;
		$section = $this->getSession('vysledky');
		$this->template->vysledky = $section->vysledkyParsed;
	}

	public function createComponentConfirmResultsForm() {
		$form = new Form;
		$form->addSubmit('send', 'Uložit výsledky');
		$form->onSuccess[] = [$this, 'confirmResultsFormSubmitted'];
		return $form;
	}

	public function confirmResultsFormSubmitted(Form $form, $values) {
		$section = $this->getSession('vysledky');
		$vysledky = $section->vysledkyParsed;
		if ($this->id !== NULL) {
			$this->zavody->deleteVysledky($this->id);
		}
		$rok = $this->zavody->getRokZavodu($this->id);

		$countSuccess = 0;
		foreach ($vysledky as $v) {
			if (!preg_match('~^\d+$~', $v['registrace'])) {
				// nepujde do zebricku, zaregistrujeme pod fiktivnim cislem
				$idZavodnika = $this->zavodnici->addUnregistered($v['prijmeni'], $v['kategorie'], $rok);
			} else {
				$zavodnik = $this->zavodnici->getZavodnik($v['registrace'], $rok);
				if ($zavodnik === NULL) {
					$idZavodnika = $this->zavodnici->addCompetitor($v['registrace'], $v['prijmeni'], $v['kategorie'], $rok);
				} else {
					$idZavodnika = $zavodnik->id;
					if (empty($zavodnik->kategorie)) {
						$this->kategorie->addCompetitorToCategory($idZavodnika, $v['kategorie'], $rok);
						// TODO rok
					}
				}
			}
			if (!empty($idZavodnika)) {
				$idZavodu = (int) $this->id;
				$this->zavody->addVysledek($idZavodu, $idZavodnika, $v['tym'], $v['cips1'], $v['umisteni1'], $v['cips2'], $v['umisteni2']);
				$countSuccess++;
			}
		}
		if ($countSuccess > 0) {
			$this->flashMessage('Do závodu bylo přidáno ' . $countSuccess . ' závodníků.');
			$this->zavody->confirmAddVysledek($this->id);
			$this->redirect('detail', $this->id);
		} else {
			$this->flashMessage('Do závodu se nepodařilo přidat žádného závodníka.');
			$this->redirect('detail', $this->id);
		}
	}

	private function trimUnicode($input) {
		return preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $input);
	}
}

