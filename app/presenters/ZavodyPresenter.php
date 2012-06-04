<?php

use \Nette\Application\UI\Form;

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class ZavodyPresenter extends BasePresenter {

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

	public function startup() {
		parent::startup();
		$this->template->registerHelper('umisteni', function($umisteni, $typZavodu) {
					$umisteni = (int) $umisteni;
					$bodovaciTabulka = Zebricek::$bodovaci_tabulky[Zebricek::$bodovani_zavodu[$typZavodu]];
					if (isset($bodovaciTabulka[$umisteni])) {
						return $bodovaciTabulka[$umisteni];
					} else {
						return "-";
					}
				});

		if ($this->getAction() != 'edit' && $this->getAction() != 'detail' && $this->getAction() != 'pridatVysledky3' && $this->getAction() != 'pridatVysledky' && $this->getAction() != 'pridatVysledky2' && $this->id !== NULL) {
			$this->id = NULL;
		}
	}

	public function renderDefault($rok = 2012) {
		$this->template->zavody = $this->context->zavody->getZavody($rok, TRUE);
		$this->template->rok = $rok;
		$this->template->typyZavodu = Zebricek::$typyZavodu;
	}

	public function actionEdit($id) {
		$this->record = $this->context->zavody->getZavod($id);

		if (!$this->record) {
			throw new \Nette\Application\BadRequestException;
		}
		$this->record->datum_od = $this->record->datum_od->format('j. n. Y');
		$this->record->datum_do = $this->record->datum_do->format('j. n. Y');
		if ($this->record->zobrazovat == 'ano')
			$this->record->zobrazovat = true; else
			$this->record->zobrazovat = false;
		if ($this->record->vysledky == 'ano')
			$this->record->vysledky = true; else
			$this->record->vysledky = false;
		$this['zavodForm']->setDefaults($this->record);
	}

	public function renderDetail($id) {
		$this->template->zavod = $this->context->zavody->getZavod($id);
		$this->template->zavodnici = $this->context->zavodnici->getZavodnici($id);
		$this->template->typyZavodu = Zebricek::$typyZavodu;
		$this->template->kategoriePrevod = Kategorie::$kategorie;
	}

	public function createComponentZavodForm() {
		$form = new Form;
		$form->addText('nazev', 'Název závodu', 50);
		$form->addText('kategorie', 'Věková kategorie závodu', 30);
		$form->addSelect('typ', 'Typ závodu', Zebricek::$typyZavodu);
		$form->addText('datum_od', 'Datum od', 10);
		$form->addText('datum_do', 'Datum do', 10);
		$form->addCheckbox('zobrazovat', 'Zobrazovat závod');
		$form->addCheckbox('vysledky', 'Jsou zadány výsledky');

		$form->addSubmit('send', 'Uložit změny');
		$form->onSuccess[] = callback($this, 'zavodFormSubmitted');
		return $form;
	}

	public function zavodFormSubmitted(Form $form) {
		if ($this->id && !$this->record) {
			throw new \Nette\Application\BadRequestException;
		}

		$values = $form->getValues();
		$values['datum_od'] = Utils::convertDate($values['datum_od']);
		$values['datum_do'] = Utils::convertDate($values['datum_do']);
		if ($values['zobrazovat'])
			$values['zobrazovat'] = 'ano'; else
			$values['zobrazovat'] = 'ne';
		if ($values['vysledky'])
			$values['vysledky'] = 'ano'; else
			$values['vysledky'] = 'ne';
		if ($this->id) {
			$this->context->zavody->updateZavod($this->record->id, $values);
			$this->flashMessage("Informace o závodu byly upraveny.", "success");
			$this->redirect("default");
		} else {
			$this->context->zavody->addZavod($values);
			$this->flashMessage("Závod byl přidán.", "success");
			$this->redirect("default");
		}
	}

	public function actionPridatVysledky($id) {
		$this->template->id = $id;
		$session = $this->context->session;
		if ($session->exists()) {
			$session = $session->start();
		}
	}

	public function createComponentVysledkyForm() {
		$form = new Form;
		$form->addTextArea('vysledky', 'Výsledky', 80, 25)
				->addRule(Form::FILLED, 'Vyplňte prosím pole s výsledky');
		$form->addSubmit('send', 'Odeslat');
		$form->onSuccess[] = callback($this, 'vysledkyFormSubmitted');
		return $form;
	}

	public function vysledkyFormSubmitted(Form $form) {
		$values = $form->getValues();
		$vysledky = $values['vysledky'];
		$session = $this->context->session;
		$section = $session->getSection('vysledky');
		$section->vysledky = $vysledky;
		$this->redirect('pridatVysledky2', $this->id);
	}

	public function actionPridatVysledky2($id) {
		$this->template->id = $id;
		$session = $this->context->session;
		$section = $session->getSection('vysledky');
		$this->vysledky = $section->vysledky;

		$lines = explode("\n", $this->vysledky);
		$pocetSloupcu = NULL;

		$tabulka = array();
		$radky = 0;

		foreach ($lines as $line) {
			$cols = explode("\t", $line);
			$sloupcu = 0;
			$radek = array();
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
					if ($radek[$i] == 'Příjmení, jméno' || $radek[$i] == 'Příjmení jméno') {
						$pravdepodobnyTyp = 'prijmeni';
						break;
					}
					if (mb_strtolower($radek[$i]) == 'kat') {
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

			$defaults['sloupec' . $i] = $pravdepodobnyTyp;
		}
		$this->template->tabulka = $tabulka;
		$this->context->session->getSection('vysledky')->tabulka = $tabulka;

		$this['vysledkyParseForm']->setDefaults($defaults);
	}

	function createComponentVysledkyParseForm() {

		$form = new Form;

		for ($i = 0; $i < $this->pocetSloupcu; $i++) {
			$form->addSelect('sloupec' . $i, '', self::$typySloupcu)->setPrompt('-');
		}

		$form->addRadioList('prvni_radek', '', range(0, $this->pocetRadku));

		$form->addSubmit('send', 'Přidat výsledky');
		$form->onSuccess[] = callback($this, 'vysledkyParseFormSubmitted');
		return $form;
	}

	public function vysledkyParseFormSubmitted(Form $form) {
		$tabulka = $this->context->session->getSection('vysledky')->tabulka;
		$values = $form->getValues();
		$sloupce = array();
		foreach ($values as $k => $v) {
			if (mb_substr($k, 0, 7) == 'sloupec') {
				$cisloSloupce = (int) str_replace('sloupec', '', $k);
				if (!empty($v))
					$sloupce[$v] = $cisloSloupce;
			}
		}
		$prvniRadek = $values['prvni_radek'];
		$radku = 0;
		$vysledky = array();
		foreach ($tabulka as $radek) {
			if ($radku++ < $prvniRadek)
				continue;
			if (!isset($radek[$sloupce['prijmeni']]))
				continue;
			$prijmeni = trim($radek[$sloupce['prijmeni']]);
			$registrace = trim($radek[$sloupce['registrace']]);
			$kategorie = trim($radek[$sloupce['kategorie']]);
			if ($prijmeni == '')
				continue;

			$poznamka = '';
			if (!preg_match('~^N?\d+$~', $registrace))
				$poznamka = 'n';
			else {
				$zavodnik = $this->context->zavodnici->getZavodnik($registrace);
				if ($zavodnik === NULL) {
					$poznamka = 'p';
				}
			}

			$tym = $radek[$sloupce['druzstvo']];

			$cips1 = trim($radek[$sloupce['cips1']]);
			$cips2 = trim($radek[$sloupce['cips2']]);
			$umisteni1 = trim($radek[$sloupce['umisteni1']]);
			$umisteni2 = trim($radek[$sloupce['umisteni2']]);

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

			$vysledky[] = array('prijmeni' => $prijmeni, 'registrace' => $registrace, 'kategorie' => $kategorie, 'tym' => $tym, 'cips1' => $cips1, 'umisteni1' => $umisteni1, 'cips2' => $cips2, 'umisteni2' => $umisteni2, 'poznamka' => $poznamka);
		}
		$this->context->session->getSection('vysledky')->vysledkyParsed = $vysledky;
		$this->redirect('pridatVysledky3', $this->id);
	}

	public function actionPridatVysledky3($id) {
		$this->template->id = $id;
		$session = $this->context->session;
		$section = $session->getSection('vysledky');
		$this->template->vysledky = $section->vysledkyParsed;
	}

	public function createComponentConfirmResultsForm() {
		$form = new Form;
		$form->addSubmit('send', 'Uložit výsledky');
		$form->onSuccess[] = callback($this, 'confirmResultsFormSubmitted');
		return $form;
	}

	public function confirmResultsFormSubmitted(Form $form) {
		$session = $this->context->session;
		$section = $session->getSection('vysledky');
		$vysledky = $section->vysledkyParsed;
		if ($this->id !== NULL) {
			$this->context->zavody->deleteVysledky($this->id);
		}

		$countSuccess = 0;
		foreach ($vysledky as $v) {
			if (!preg_match('~^N?\d+$~', $v['registrace'])) {
				// nepujde do zebricku
			} else {
				$zavodnik = $this->context->zavodnici->getZavodnik($v['registrace']);
				if ($zavodnik === NULL) {
					$idZavodnika = $this->context->zavodnici->addZavodnik($v['registrace'], $v['prijmeni'], $v['kategorie']);
				} else {
					$idZavodnika = $zavodnik->id;
					if (empty($zavodnik->kategorie)) {
						$this->context->kategorie->addZavodnikKategorie($idZavodnika, $v['kategorie'], 2012);
						// TODO rok
					}
				}
				if (!empty($idZavodnika)) {
					$idZavodu = (int) $this->id;
					$this->context->zavody->addVysledek($idZavodu, $idZavodnika, $v['tym'], $v['cips1'], $v['umisteni1'], $v['cips2'], $v['umisteni2']);
					$countSuccess++;
				}
			}
		}
		if ($countSuccess > 0) {
			$this->flashMessage('Do závodu bylo přidáno ' . $countSuccess . ' závodníků.');
			$this->context->zavody->confirmAddVysledek($this->id);
			$this->redirect('detail', $this->id);
		} else {
			$this->flashMessage('Do závodu se nepodařilo přidat žádného závodníka.');
			$this->redirect('detail', $this->id);
		}
	}

}

