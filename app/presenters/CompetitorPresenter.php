<?php
declare(strict_types=1);

namespace App\Presenters;

use App\Exceptions\CompetitorNotFoundException;
use App\Model\Competitors;
use App\Model\Leagues;
use App\Model\Ranking;
use App\Model\Teams;
use Nette\Application\BadRequestException;

final class CompetitorPresenter extends BasePresenter
{

	/** @var Ranking */
	private $zebricek;

	/** @var Teams */
	private $teams;

	/** @var Leagues */
	private $leagues;

	/** @var Competitors */
	private $competitors;

	public function __construct(Ranking $ranking, Teams $teams, Leagues $leagues, Competitors $competitors)
	{
		parent::__construct();
		$this->zebricek = $ranking;
		$this->teams = $teams;
		$this->leagues = $leagues;
		$this->competitors = $competitors;
	}

	public function renderDefault(string $id, string $year): void
	{
		try {
			$competitor = $this->competitors->getCompetitorWithCategoryById((int)$id, (int)$year);
			$detail = $this->zebricek->getResultsForYear($competitor);
			$this->template->rok = $year;
			$this->template->zavodnik = $competitor;
			$this->template->vysledky = $detail['vysledky'];
			$this->template->vysledkyCelkovy = $detail['vysledky_celkovy'];
			$this->template->vysledkyDorost = $detail['vysledky_dorost'];
			$this->template->vysledkyZeny = $detail['vysledky_zeny'];
			$this->template->ligy = $this->leagues->getLeaguesForYear((int)$year);
			$this->template->clenstvi = $this->teams->loadTeamMembership((int)$id, (int)$year);
		} catch (CompetitorNotFoundException $exc) {
			throw new BadRequestException($exc->getMessage());
		}
	}

}
