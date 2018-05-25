<?php
declare(strict_types=1);

namespace App\FrontModule\Presenters;

use App\Model\Competitions;
use App\Model\ExcelExport;
use App\Model\Ranking;
use App\Model\Rankings;
use App\Model\RankingType;
use Nette\Application\Responses\FileResponse;

final class HomepagePresenter extends BasePresenter
{

	/** @var \App\Model\Ranking */
	private $ranking;

	/** @var \App\Model\Competitions */
	private $competitions;

	/** @var ExcelExport */
	private $excelExport;

	/** @var Rankings */
	private $rankings;

	public function __construct(Ranking $ranking, Competitions $competitions, ExcelExport $excelExport, Rankings $rankings)
	{
		parent::__construct();
		$this->ranking = $ranking;
		$this->competitions = $competitions;
		$this->excelExport = $excelExport;
		$this->rankings = $rankings;
	}

	public function renderDefault(string $year, string $type = 'celkem', $show = false): void
	{
		$selectedYear = (int)$year;

		$this->template->rankings = $this->rankings->getByYear($selectedYear);
		$this->template->typ = $type;
		$this->template->typZebricku = $this->rankings->translate($type);
		$this->template->zobrazitZavody = $show;
		$this->template->validityDate = $this->ranking->getValidityDate($selectedYear);
		$this->template->zavody = $competitions = $this->ranking->getCompetitions($selectedYear);
		$this->template->rok = $selectedYear;
		$this->template->zavodnici = $this->ranking->getRanking($selectedYear, $competitions, new RankingType($type));
		$this->template->chybejiciVysledky = $this->competitions->loadWithMissingResults($selectedYear);
	}

	public function handleExcelExport(string $year): void
	{
		$selectedYear = (int)$year;
		$competitions = $this->ranking->getCompetitions($selectedYear);
		$datumPlatnosti = $this->ranking->getValidityDate($selectedYear);
		$rankings = $this->rankings->getByYear((int)$year);
		$allRankings = [];
		foreach ($rankings as $shortcut => $fullName) {
			$rt = new RankingType($shortcut);
			$data = $this->ranking->getRanking($selectedYear, $competitions, $rt);
			$allRankings[] = [
				'sheetTitle' => $this->rankings->toExcelTitle($shortcut),
				'title' => 'Průběžný žebříček LRU plavaná - ' . $this->rankings->toExcelTitle($shortcut),
				'data' => $data,
				'validityDate' => $datumPlatnosti,
				'filterArg' => $shortcut !== 'celkem' ? $shortcut : '',
			];
		}

		$tn = $this->excelExport->doSomething2($datumPlatnosti, $selectedYear, $allRankings);

		if ($datumPlatnosti !== null) {
			$name = "zebricek-" . $datumPlatnosti->format('Ymd') . ".xlsx";
		} else {
			$name = "zebricek-" . $year . ".xlsx";
		}
		$response = new FileResponse($tn, $name, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', true);
		$this->sendResponse($response);
	}
}
