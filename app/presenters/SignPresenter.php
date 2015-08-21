<?php

namespace App\Presenters;

use Nette\Application\UI\Form,
	Nette\Security as NS;

final class SignPresenter extends BasePresenter {

	protected function createComponentSignInForm() {
		$form = new Form;
		$form->addText('username', 'Uživatelské jméno:')
			->setRequired('Zadejte prosím uživatelské jméno.');

		$form->addPassword('password', 'Heslo:')
			->setRequired('Zadejte prosím heslo.');

		$form->addCheckbox('remember', 'Zapamatovat');

		$form->addSubmit('send', 'Přihlásit');

		$form->onSuccess[] = $this->signInFormSuccess;
		return $form;
	}

	public function signInFormSuccess(Form $form, $values) {
		try {
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
		$this->flashMessage('Byl(a) jste odhlášen(a).');
		$this->redirect('in');
	}

}
