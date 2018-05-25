<?php
declare(strict_types=1);

namespace App\FrontModule\Presenters;

use App\Exceptions\CategoryNotFoundException;
use App\Exceptions\RegistrationAlreadyExistsException;
use App\Model\Categories;
use App\Model\Category;
use App\Model\Competitors;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Http\IResponse;
use Nette\Utils\Html;

final class CompetitorsPresenter extends BasePresenter
{

	/** @var \App\Model\Competitors */
	private $competitors;

	/** @var Categories */
	private $categories;

	/** @var int|null */
	private $competitorId = null;

	/** @var array */
	private $loadedCategories = [];

	public function __construct(Competitors $competitors, Categories $categories)
	{
		parent::__construct();
		$this->competitors = $competitors;
		$this->categories = $categories;
	}

	public function startup(): void
	{
		parent::startup();
		if (!$this->getUser()->isInRole('admin')) {
			throw new BadRequestException('Pro správu závodníků musíte být správce.', IResponse::S403_FORBIDDEN);
		}
	}

	public function actionDefault(string $year): void
	{
		$this->template->rok = $year;
	}

	public function actionEdit(string $year, string $id): void
	{
		$this->competitorId = (int)$id;
		$this->template->competitor = $c = $this->competitors->getById((int)$id);
		$this->loadedCategories = $this->competitors->loadCategories((int)$id);
		if (!isset($this->loadedCategories[$year])) {
			$this->loadedCategories[$year] = Category::fromString(null);
		}
		$this->template->categories = $this->loadedCategories;
		$this['changeNameForm']->setDefaults(['name' => $c->getFullName()]);
	}

	public function renderResults(string $year, string $term): void
	{
		$this->template->results = $this->competitors->search($term);
		$this->template->year = $year;
	}

	public function createComponentSearchForm(): Form
	{
		$form = new Form();
		$form->addText('search', 'Jméno nebo číslo registrace')->setRequired('Zadejte jméno nebo číslo registrace')->setHtmlAttribute('autofocus');
		$form->addSubmit('send', 'Hledat');
		$form->onSuccess[] = function (Form $form, $values): void {
			$this->redirect('results', $this->year, $values->search);
		};
		return $form;
	}

	public function createComponentChangeNameForm(): Form
	{
		$form = new Form();
		$form->addText('name', 'Nové jméno')->setRequired('Vyplňte prosím nové jméno závodníka');
		$form->addSubmit('save', 'Uložit');
		$form->onSuccess[] = function (Form $form, $values): void {
			if ($this->competitorId !== null) {
				$this->competitors->changeName($this->competitorId, $values->name);
				$this->flashMessage('Jméno závodníka bylo změněno.');
				$this->redirect('this');
			}
		};
		return $form;
	}

	protected function createComponentChangeRegistrationForm(): Form {
		$form = new Form();
		$form->addText('registration', 'Nové číslo registrace')->setRequired('Vyplňte prosím nové číslo registrace');
		$form->addSubmit('save', 'Uložit');
		$form->onSuccess[] = function(Form $form, $values): void {
			if ($this->competitorId !== null) {
				try {
					$this->competitors->changeRegistration($this->competitorId, $values->registration);
					$this->flashMessage('Číslo registrace závodníka bylo změněno.');
					$this->redirect('this');
				} catch (RegistrationAlreadyExistsException $exc) {
					$form->addError($exc->getMessage().'. Změna nebyla provedena.');
				}
			}
		};
		return $form;
	}

	public function createComponentChangeCategoryForm(): Form
	{
		$form = new Form();
		foreach ($this->loadedCategories as $year => $category) {
			$form->addSelect('category_' . $year, 'Kategorie', $this->categories->getCompetitorCategoriesByYear($year))->setPrompt('-- zvolte novou kategorii --');
		}
		$form->addSubmit('save', 'Uložit');
		$form->onSuccess[] = function (Form $form, $values): void {
			$this->changeCategoryFormSucceeded($form, $values);
		};
		return $form;
	}

	public function changeCategoryFormSucceeded(Form $form, $values): void
	{
		$needsRedirect = false;
		foreach ($values as $k => $v) {
			if ($v !== null && preg_match('~^category_(\d+)$~', $k, $m)) {
				try {
					$c = Category::fromString($v);
					if ($this->competitorId !== null) {
						$this->competitors->changeCategory($this->competitorId, (int)$m[1], $c);
						$el = Html::el()->addText('Kategorie v roce ' . $m[1] . ' byla změněna na ')->addHtml(Html::el('b')->addText($c->toCzechString()));
						$this->flashMessage($el);
						$needsRedirect = true;
					}
				} catch (CategoryNotFoundException $exc) {
					$form[$k]->addError('Nepodařilo se rozpoznat vybranou kategorii u roku '.$m[1]);
				}

			}
		}
		if ($needsRedirect) {
			$this->redirect('this');
		}
	}
}
