<?php

/**
 * Base class for all application presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter {

	public function startup() {
		parent::startup();
		\Nette\Application\UI\Form::extensionMethod('\Nette\Application\UI\Form::addSuggestInput', 'Nette\Forms\Controls\SuggestInput::addSuggestInput');
	}

}
