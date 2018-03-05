<?php
declare(strict_types=1);

namespace App\Presenters;

use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;

final class SignPresenter extends BasePresenter
{
	protected function createComponentSignInForm(): Form
	{
		$form = new Form;
		$form->addText('username', 'Uživatelské jméno:')
			->setRequired('Zadejte prosím uživatelské jméno.');

		$form->addPassword('password', 'Heslo:')
			->setRequired('Zadejte prosím heslo.');

		$form->addCheckbox('remember', 'Zapamatovat');

		$form->addSubmit('send', 'Přihlásit');

		$form->onSuccess[] = function (Form $form, $values) {
			$this->signInFormSuccess($form, $values);
		};
		return $form;
	}

	public function signInFormSuccess(Form $form, $values): void
	{
		try {
			if ($values->remember) {
				$this->getUser()->setExpiration('+ 14 days', false);
			} else {
				$this->getUser()->setExpiration('+ 20 minutes', true);
			}
			$this->getUser()->login($values->username, $values->password);
			$this->redirect('Homepage:');
		} catch (AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}

	public function actionOut(): void
	{
		$this->getUser()->logout();
		$this->flashMessage('Byl(a) jste odhlášen(a).');
		$this->redirect('in');
	}

}
