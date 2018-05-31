<?php
declare(strict_types=1);

namespace App\FrontModule\Presenters;

use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;

final class SignPresenter extends BasePresenter
{

	/** @persistent */
	public $backlink = '';

	protected function createComponentSignInForm(): Form
	{
		$form = new Form();
		$form->addText('username', 'Uživatelské jméno:')
			->setRequired('Zadejte prosím uživatelské jméno.')
			->setAttribute('autofocus');

		$form->addPassword('password', 'Heslo:')
			->setRequired('Zadejte prosím heslo.');

		$form->addCheckbox('remember', 'Zapamatovat');

		$form->addSubmit('send', 'Přihlásit');

		$form->onSuccess[] = function (Form $form, $values) {
			try {
				if ($values->remember) {
					$this->getUser()->setExpiration('+ 14 days', false);
				} else {
					$this->getUser()->setExpiration('+ 20 minutes', true);
				}
				$this->getUser()->login($values->username, $values->password);
				$this->restoreRequest($this->backlink);
				$this->redirect('Homepage:');
			} catch (AuthenticationException $e) {
				$form->addError($e->getMessage());
			}
		};
		return $form;
	}

	public function actionOut(): void
	{
		$this->getUser()->logout();
		$this->flashMessage('Byl(a) jste odhlášen(a).');
		$this->redirect('in');
	}

}
