<?php
use \Nette\Application\UI\Form;
/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class ZavodyPresenter extends BasePresenter {

    public function startup() {
	parent::startup();
	$this->template->registerHelper('umisteni', function($umisteni, $typZavodu) {
	    $umisteni = (int) $umisteni;
	    $bodovaciTabulka = Zavody::$bodovaci_tabulky[Zavody::$bodovani_zavodu[$typZavodu]];
	    if (isset($bodovaciTabulka[$umisteni])) {
		return $bodovaciTabulka[$umisteni];
	    } else {
		return "-";
	    }
	});
    }
    
    public function renderDefault($rok = 2012) {
	$this->template->zavody = $this->context->zavody->getZavody($rok);
	$this->template->rok = $rok;
	$this->template->typyZavodu = Zavody::$typyZavodu;
    }
    
    public function renderEdit($id) {
	$form = $this['editForm'];
	$zavod = $this->context->zavody->getZavod($id);
	$form->setDefaults($zavod);
    }
    
    public function renderDetail($id) {
	$this->template->zavod = $this->context->zavody->getZavod($id);
	$this->template->zavodnici = $this->context->zavodnici->getZavodnici($id);
	$this->template->typyZavodu = Zavody::$typyZavodu;
    }
    
        
    public function createComponentEditForm() {
	$form = new Form;
	$form->addHidden('id');
	$form->addText('nazev', 'Název závodu', 50);
	$form->addSelect('typ', 'Typ závodu', Zavody::$typyZavodu);
	$form->addText('datum_od', 'Datum od', 10);
	$form->addText('datum_do', 'Datum do', 10);
	
	$form->addSubmit('send', 'Uložit změny');
	$form->onSuccess[] = callback($this, 'editFormSubmitted');
	return $form;
    }
    
    public function editFormSubmitted(Form $form) {
	
    }
}
