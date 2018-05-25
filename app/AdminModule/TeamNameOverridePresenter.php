<?php
declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\Exceptions\CompetitorNotFoundException;
use App\Model\Competitors;
use App\Model\Suggest;
use App\Model\TeamMembersCount;
use App\Model\TeamNameOverrides;
use App\Model\Years;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Http\IResponse;
use Nette\Utils\Html;

final class TeamNameOverridePresenter extends BasePresenter
{

	/** @var TeamNameOverrides */
	private $teamNameOverrides;

	/** @var Years */
	private $years;

	/** @var Suggest */
	private $suggest;

	/** @var Competitors */
	private $competitors;

	/** @var array */
	private $yearsForSelect = [];

	public function __construct(TeamNameOverrides $teamNameOverrides, Years $years, Suggest $suggest, Competitors $competitors)
	{
		parent::__construct();
		$this->teamNameOverrides = $teamNameOverrides;
		$this->years = $years;
		$this->suggest = $suggest;
		$this->competitors = $competitors;
	}

	public function actionDefault(): void
	{
		$this->yearsForSelect = $this->years->loadAll();
	}

	public function renderDefault(): void
	{
		$this->template->overrides = $this->teamNameOverrides->getAll();
	}

	public function actionSuggest($typedText): void
	{
		$this->sendJson(['values' => $this->suggest->getSuggest($typedText)]);
	}

	public function handleRemove(string $id, string $year): void
	{
		$this->teamNameOverrides->remove((int)$id, (int)$year);
		$this->redirect('this');
	}

	protected function createComponentOverrideForm(): Form
	{
		$form = new Form();
		$form->addSelect('year', 'Rok', $this->yearsForSelect)->setPrompt('-- zvolte rok --')->setRequired('Vyberte rok, pro který chcete nový název týmu zadat');
		$form->addText('competitor', 'Závodník')->setRequired('Vyberte závodníka')->getControlPrototype()->addAttributes(['class' => 'naseptavac']);
		$form->addText('team', 'Název týmu')->setRequired('Zadejte nový název týmu');
		$form->addSubmit('send', 'Uložit');
		$form->onSuccess[] = function (Form $form, $values) {
			try {
				if (preg_match('~^\d+$~', $values->competitor)) {
					$zavodnik = $this->competitors->getByRegistration($values->competitor);
				} else {
					$zavodnik = $this->competitors->getByName($values->competitor);
				}
				$this->teamNameOverrides->add((int)$values->year, $zavodnik->getId(), $values->team);
				$msg = Html::el()->addText('Pro závodníka ')->addHtml(Html::el('b', $zavodnik->getFullName()))->addText(' a rok ')->addHtml(Html::el('b', $values->year))->addText(' byl uložen nový název týmu ')->addHtml(Html::el('b', $values->team))->addText('.');
				$this->flashMessage($msg);
				$this->redirect('this');
			} catch (CompetitorNotFoundException $exc) {
				$form->addError('Nepodařilo se najít závodníka.');
			}
		};
		return $form;
	}

}
