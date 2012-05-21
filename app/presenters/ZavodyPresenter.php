<?php

use \Nette\Application\UI\Form;

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class ZavodyPresenter extends BasePresenter {

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

        if ($this->getAction() != 'edit' && $this->getAction() != 'detail' && $this->getAction() != 'pridatVysledky' && $this->getAction() != 'pridatVysledky2' && $this->id !== NULL) {
            $this->id = NULL;
        }
    }

    public function renderDefault($rok = 2012) {
        $this->template->zavody = $this->context->zavody->getZavody($rok);
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
    }

    public function createComponentZavodForm() {
        $form = new Form;
        $form->addText('nazev', 'Název závodu', 50);
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
        if ($session->exists()) {
            $session = $session->start();
        }
        
        $section = $session->getSection('vysledky');
        $section->vysledky = $vysledky;
        $this->redirect('pridatVysledky2', $this->id);
    }

    public function actionPridatVysledky2($id) {
        $this->template->id = $id;
        
        $session = $this->context->session;
        if ($this->context->session->exists()) {
            $session = $this->context->session->start();
        }
        $section = $session->getSection('vysledky');
        $this->vysledky = $section->vysledky;
        
        
    }

    public function createComponentVysledkyParseForm() {
        $form = new Form;
        \Nette\Diagnostics\Debugger::dump($this->vysledky);
        $form->addSubmit('send', 'Přidat výsledky');
        $form->onSuccess[] = callback($this, 'vysledkyParseFormSubmitted');
        return $form;
    }

}
