<?php

use Nette\Application\UI,
	Nette\Security as NS;

/**
 * Sign in/out presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class SignPresenter extends BasePresenter {

	/**
	 * Sign in form component factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm() {
		$form = new UI\Form;
		$form->addText('username', 'Uživatelské jméno:')
				->setRequired('Zadejte prosím uživatelské jméno.');

		$form->addPassword('password', 'Heslo:')
				->setRequired('Zadejte prosím heslo.');

		$form->addCheckbox('remember', 'Zapamatovat');

		$form->addSubmit('send', 'Sign in');

		$form->onSuccess[] = callback($this, 'signInFormSubmitted');
		return $form;
	}

	public function signInFormSubmitted($form) {
		try {
			$values = $form->getValues();
			if ($values->remember) {
				$this->getUser()->setExpiration('+ 14 days', FALSE);
			} else {
				$this->getUser()->setExpiration('+ 20 minutes', TRUE);
			}
			$this->getUser()->login($values->username, $values->password);
			$this->redirect('Homepage:');
		} catch (NS\AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}

	public function actionOut() {
		$this->getUser()->logout();
		$this->flashMessage('You have been signed out.');
		$this->redirect('in');
	}

}
