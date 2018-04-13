<?php
declare(strict_types=1);

namespace App\Presenters;

use App\Exceptions\CompetitorNotFoundException;
use App\Model\Competitors;
use App\Model\Leagues;
use App\Model\Suggest;
use App\Model\Team;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use App\Model\Teams;
use Nette\Http\IResponse;

final class TeamsPresenter extends BasePresenter
{

	/** @var \App\Model\Teams */
	private $teams;

	/** @var \App\Model\Suggest */
	private $suggest;

	/** @var \App\Model\Leagues */
	private $leagues;

	/** @var \App\Model\Competitors */
	private $competitors;

	/** @var int|null */
	private $teamId;

	/** @var array */
	private $leaguesForYear = [];

	/** @var array */
	private $loadedTeams = [];

	/** @var null|string */
	private $league = null;

	public function __construct(Teams $teams, Suggest $suggest, Leagues $leagues, Competitors $competitors)
	{
		parent::__construct();
		$this->teams = $teams;
		$this->suggest = $suggest;
		$this->leagues = $leagues;
		$this->competitors = $competitors;
	}

	public function startup(): void {
		parent::startup();
		if (!\in_array($this->getAction(), ['default', 'detail'], true) && !$this->getUser()->isInRole('admin')) {
			throw new BadRequestException('Pro vstup do přidávání týmů musíte mít oprávnění správce.', IResponse::S403_FORBIDDEN);
		}
	}

	public function actionDefault(string $year): void
	{
		$this->template->leagues = $this->leagues->getLeaguesForYear((int)$year);
		$this->template->year = $year;
		$this->template->teams = $this->teams->loadTeamsByYear((int)$year);
	}

	public function actionDetail(string $id): void
	{
		$this->teamId = (int)$id;
		$this->template->team = $ti = $this->teams->getTeamInfo((int)$id);
		$this->year = $ti->year;
		$this->template->leagues = $this->leagues->getLeaguesForYear($this->year);
		$this->template->members = $m = $this->teams->loadMembers((int)$id);
		$index = 1;
		$defaults = [];
		foreach ($m as $z) {
			$defaults['zavodnik' . $index] = $z->getFullName();
			$index++;
		}
		$this['addForm']->setDefaults($defaults);
	}

	public function actionSelectLeague(string $year): void
	{
		$this->year = (int)$year;
		$this->leaguesForYear = $this->leagues->getLeaguesForYear((int)$year);
	}

	public function actionAdd(string $year, string $league, string $count): void
	{
		$this->year = (int)$year;
		$this->league = $league;
		$this->teams->generateMissingTeams((int)$year, $league, (int)$count);
		$this->template->leagueName = $this->leagues->getName($league);
		$this->template->year = $year;
		$this->loadedTeams = $this->teams->loadByYearAndLeague((int)$year, $league);
		$defaults = [];
		foreach ($this->loadedTeams as $t) {
			$defaults['team_' . $t->getId()] = $t->getName();
		}
		$this['leagueTeamsForm']->setDefaults($defaults);
	}

	protected function createComponentSelectLeagueForm(): Form
	{
		$form = new Form();
		$form->addSelect('league', 'Liga', $this->leaguesForYear)->setPrompt('-- vyberte ligu --')->setRequired('Prosím vyberte ligu');
		$form->addInteger('count', 'Maximální počet týmů ligy')->setRequired('Prosím zadejte maximální počet týmů ligy')->addRule(Form::RANGE, 'Maximální počet týmů ligy musí být mezi %d a %d', [1, 30]);
		$form->addSubmit('save', 'Vybrat ligu');
		$form->onSuccess[] = function (Form $form, $values) {
			$this->redirect('add', $this->year, $values->league, $values->count);
		};
		return $form;
	}

	protected function createComponentLeagueTeamsForm(): Form
	{
		$form = new Form();
		/** @var Team $t */
		foreach ($this->loadedTeams as $t) {
			$form->addText('team_' . $t->getId(), 'Název týmu ' . $t->getCode());
		}
		$form->addSubmit('save', 'Uložit týmy');
		$form->onSuccess[] = function (Form $form, $values): void {
			$this->leagueTeamsFormSucceeded($form, $values);
		};
		return $form;
	}


	public function leagueTeamsFormSucceeded(Form $form, $values): void
	{
		foreach ($values as $k => $v) {
			if (preg_match('~^team_(\d+)$~', $k, $m)) {
				$this->teams->rename((int)$m[1], $v);
			}
		}
		$this->redirect('this');
	}

	public function createComponentAddForm(): Form
	{
		$form = new Form();
		for ($i = 1; $i <= 13; $i++) {
			$form->addText('zavodnik' . $i, $i, 40)->getControlPrototype()->addAttributes(['class' => 'naseptavac']);
		}

		$form->addSubmit('send', 'Uložit závodníky');
		$form->onSuccess[] = function (Form $form, $values) {
			$this->addFormSubmitted($form, $values);
		};
		return $form;
	}

	public function addFormSubmitted(Form $form, $values): void
	{
		if ($this->teamId !== null) {
			$this->teams->removeAllMembersFromTeam($this->teamId);
			foreach ($values as $k => $v) {
				if (mb_substr($k, 0, 8) !== 'zavodnik') {
					continue;
				}
				if (trim($v) === '')
					continue;
				try {
					if (preg_match('~^\d+$~', $v)) {
						$zavodnik = $this->competitors->getByRegistration($v);
					} else {
						$zavodnik = $this->competitors->getByName($v);
					}

					$this->teams->addTeamMember($this->teamId, $zavodnik->getId());
					$category = $this->competitors->getCompetitorCategory($this->year, $zavodnik->getId());
					$this->competitors->setCompetitorCategory($this->year, $zavodnik->getId(), $category);
					$this->flashMessage('Závodník ' . $v . ' byl přidán do týmu ID = ' . $this->teamId);

				} catch (CompetitorNotFoundException $exc) {
					$this->flashMessage('Závodníka ' . $v . ' se nepodařilo vyhledat.', 'error');
				}
			}
			$this->redirect('detail', $this->teamId);
		}
	}

	public function actionSuggest($typedText): void
	{
		$this->sendJson(['values' => $this->suggest->getSuggest($typedText)]);
	}

}
