<?php

namespace App\Model;

use Nette\Database\Connection;

final class Ranking extends Base
{

	/** @var Competitions */
	private $competitions;

	/** @var ScoringTables */
	private $scoringTables;

	/** @var TeamNameOverrides */
	private $teamNameOverrides;

	/** @var CountedCompetitions */
	private $countedCompetitions;

	public function __construct(Connection $database, Competitions $competitions, ScoringTables $scoringTables, TeamNameOverrides $teamNameOverrides, CountedCompetitions $countedCompetitions)
	{
		parent::__construct($database);
		$this->competitions = $competitions;
		$this->scoringTables = $scoringTables;
		$this->teamNameOverrides = $teamNameOverrides;
		$this->countedCompetitions = $countedCompetitions;
	}

	public function getValidityDate(int $year) {
		return $this->database->query("SELECT MAX(`datum_do`) `datum_platnosti` FROM `zavody` WHERE `rok` = ? AND zobrazovat = 'ano' AND vysledky = 'ano'", $year)->fetchField();
	}

	public function getCompetitions(int $year): array {
		$competitions = [];
		$visibleCompetitions = $this->competitions->loadVisibleCompetitions($year);
		foreach ($visibleCompetitions as $c) {
			$competitions[$c->getId()] = ['type' => $c->getType(), 'title_with_category' => $c->getTitle()];
		}
		return $competitions;
	}

	public function getRanking(int $year, array $competitions, RankingType $type): array
	{
		$countedCompetitions = $this->countedCompetitions->getByYear($year);
		$competitors = [];
		$query = "SELECT `z`.`cele_jmeno`, `z`.`registrace`, `zz`.`id_zavodnika`, `zz`.`id_zavodu`, `zav`.`kategorie` `kategorie_zavodu`, `zz`.`tym`, `zk`.`kategorie`, `cips1`, `umisteni1`, `cips2`, `umisteni2` FROM `zavodnici_zavody` `zz` JOIN `zavodnici` `z` ON `zz`.`id_zavodnika` = `z`.`id` JOIN `zavody` `zav` ON `zz`.`id_zavodu` = `zav`.`id` JOIN `zavodnici_kategorie` `zk` ON `zz`.`id_zavodnika` = `zk`.`id_zavodnika` WHERE `zk`.`rok` = `zav`.`rok` AND `z`.`registrovany` = 'A' AND (`cips1` IS NOT NULL OR `cips2` IS NOT NULL) AND (`zav`.`zobrazovat` = 'ano') AND (`zav`.`vysledky` = 'ano') AND `zav`.`rok` = ? ";
		$args = [$year];
		if (\count($type->getDbCategoryValues()) > 0) {
			$query .= " AND `zk`.`kategorie` IN (?) AND `zav`.`kategorie` != 'zeny'";
			$args[] = $type->getDbCategoryValues();
		}
		if ($type->getValue() === RankingType::WOMEN) {
			$query .= " AND `zk`.`kategorie` IN ('u10_zena', 'u14_zena', 'u18_zena', 'u23_zena', 'zena', 'u12_zena', 'u15_zena', 'u20_zena', 'u25_zena') AND (`zav`.`kategorie` = '' OR `zav`.`kategorie` = 'zeny')";
		}

		$result = $this->database->queryArgs($query . " ORDER BY `zav`.`datum_od`, `zav`.`nazev`", $args);
		foreach ($result as $row) {
			$id = $row->id_zavodnika;
			if (!isset($competitors[$id])) {
				$competitors[$id] = [
					'jmeno' => $row->cele_jmeno,
					'min_body_zebricek' => 0,
					'zavodu' => 0,
					'registrace' => $row->registrace,
					'tym' => $row->tym,
					'kategorie' => Category::fromString($row->kategorie),
					'body_celkem' => [],
					'body_zebricek' => [],
					'cips_celkem' => [],
					'vysledky' => [],
				];
			}
			$competitors[$id]['vysledky'][$row->id_zavodu] = [
				'zavod' => $row->id_zavodu,
				'kategorie_zavodu' => $row->kategorie_zavodu,
				'umisteni1' => $row->umisteni1,
				'umisteni2' => $row->umisteni2,
				'cips1' => $row->cips1,
				'cips2' => $row->cips2,
				'body' => 0,
			];
			if ($competitors[$id]['tym'] === null || $competitors[$id]['tym'] === '')
				$competitors[$id]['tym'] = $row->tym;
			if ($row->umisteni1 !== null)
				$competitors[$id]['zavodu']++;
			if ($row->umisteni2 !== null)
				$competitors[$id]['zavodu']++;
		}

		if (count($competitors) > 0) {
			$result = $this->database->query("SELECT `tz`.`id_zavodnika`, `t`.`nazev_tymu`, 
(SELECT MIN(`poradi`) FROM `tymy_zavodnici` `tz2` WHERE `tz2`.`id_tymu` = `tz`.`id_tymu` AND `tz2`.`id_zavodnika` = `tz`.`id_zavodnika`) `pocet`,
(SELECT COUNT(*) FROM `tymy_zavodnici` `tz2` WHERE `tz2`.`id_tymu` = `tz`.`id_tymu`) `procento`
FROM `tymy_zavodnici` `tz` JOIN `tymy` `t` ON `tz`.`id_tymu` = `t`.`id` WHERE `tz`.`id_zavodnika` IN (?) AND `rok` = ? ORDER BY id_zavodnika, `pocet` / `procento` DESC", array_keys($competitors), $year);
			foreach ($result as $row) {
				$competitors[$row->id_zavodnika]['tym'] = $row->nazev_tymu;
			}
		}
		$overrides = $this->teamNameOverrides->getByYear($year);
		foreach ($overrides as $competitorId => $newTeamName) {
			if (isset($competitors[$competitorId])) {
				$competitors[$competitorId]['tym'] = $newTeamName;
			}
		}

		foreach ($competitors as $id => $z) {
			foreach ($z['vysledky'] as $k => $v) {
				$idZavodu = $v['zavod'];
				$kategorieZavodu = $v['kategorie_zavodu'];
				$typZavodu = $competitions[$idZavodu]['type'];

				$body1 = $this->getPoints($typZavodu, $v['umisteni1']);
				$body2 = $this->getPoints($typZavodu, $v['umisteni2']);
				$cips1 = $v['cips1'];
				$cips2 = $v['cips2'];

				if ($v['umisteni1'] !== null) {
					$competitors[$id]['vysledky'][$k]['body1'] = $body1;
					$competitors[$id]['vysledky'][$k]['body1_zebricek'] = false;
					$competitors[$id]['body_celkem'][] = $body1;
					if ($type->getValue() !== RankingType::TOTAL || $kategorieZavodu == '')
						$competitors[$id]['body_zebricek'][] = $body1;
					$competitors[$id]['cips_celkem'][] = $cips1;
				} else {
					$competitors[$id]['vysledky'][$k]['body1'] = null;
					$competitors[$id]['vysledky'][$k]['body1_zebricek'] = false;
				}
				if ($v['umisteni2'] !== null) {
					$competitors[$id]['vysledky'][$k]['body2'] = $body2;
					$competitors[$id]['vysledky'][$k]['body2_zebricek'] = false;
					$competitors[$id]['body_celkem'][] = $body2;
					if ($type->getValue() !== RankingType::TOTAL || $kategorieZavodu == '')
						$competitors[$id]['body_zebricek'][] = $body2;
					$competitors[$id]['cips_celkem'][] = $cips2;
				} else {
					$competitors[$id]['vysledky'][$k]['body2'] = null;
					$competitors[$id]['vysledky'][$k]['body2_zebricek'] = false;
				}
			}
		}
		foreach ($competitors as $id => $z) {
			if (count($competitors[$id]['body_zebricek']) > $countedCompetitions) {
				rsort($competitors[$id]['body_zebricek']);
				$competitors[$id]['body_zebricek'] = array_slice($competitors[$id]['body_zebricek'], 0, $countedCompetitions);
			}
			if (count($competitors[$id]['body_zebricek']) > $countedCompetitions - 1) {
				$temp = array_values($competitors[$id]['body_zebricek']);
				rsort($temp);
				$competitors[$id]['min_body_zebricek'] = array_pop($temp) + 1;
			} else {
				$competitors[$id]['min_body_zebricek'] = 0;
			}

			$bodyZebricekKopie = $competitors[$id]['body_zebricek'];
			foreach ($z['vysledky'] as $k => $v) {
				$body1 = $competitors[$id]['vysledky'][$k]['body1'];
				if ($body1 !== null) {
					$ind = array_search($body1, $bodyZebricekKopie, true);
					if ($ind !== false) {
						$competitors[$id]['vysledky'][$k]['body1_zebricek'] = true;
						unset($bodyZebricekKopie[$ind]);
					}
				}
				$body2 = $competitors[$id]['vysledky'][$k]['body2'];
				if ($body2 !== null) {
					$ind = array_search($body2, $bodyZebricekKopie, true);
					if ($ind !== false) {
						$competitors[$id]['vysledky'][$k]['body2_zebricek'] = true;
						unset($bodyZebricekKopie[$ind]);
					}
				}
			}
		}

		uasort($competitors, function ($a, $b) {
			return $this->bodySort($a, $b);
		});
		return $competitors;
	}

	private function getPoints(string $competitionType, $position): ?int
	{
		$bodovaciTabulka = $this->scoringTables->getByCompetitionType($competitionType);
		if ($position === null)
			return null;
		$position = (int)$position;
		if (isset($bodovaciTabulka[$position]))
			$body = $bodovaciTabulka[$position];
		else
			$body = 0;
		return $body;
	}

	public function getResultsForYear(CompetitorWithCategoryAndYear $c): array
	{
		$allResults = $this->loadAllResultsForCompetitorAndYear($c->getId(), $c->getYear(), 'všechny');

		$juvenileResults = null;
		$womenResults = null;
		$completeResults = $this->loadAllResultsForCompetitorAndYear($c->getId(), $c->getYear(), null);

		if ($c->getCategory()->isWomen()) {
			$womenResults = $this->loadAllResultsForCompetitorAndYear($c->getId(), $c->getYear(), 'ženy');
		}

		if ($c->getCategory()->isU()) {
			$juvenileResults = $this->loadAllResultsForCompetitorAndYear($c->getId(), $c->getYear(), $c->getCategory()->getBaseForU());
		}

		return [
			'vysledky' => $allResults['vysledky'] ?? null,
			'vysledky_celkovy' => $completeResults,
			'vysledky_zeny' => $womenResults,
			'vysledky_dorost' => $juvenileResults,
		];
	}

	private function loadAllResultsForCompetitorAndYear($competitorId, int $rok, $omezeni = null): array
	{
		$countedCompetitions = $this->countedCompetitions->getByYear($rok);

		$competitionCount = 0;
		$totalPoints = [];
		$competitionResults = [];

		$query = "SELECT
					`zav`.`id` `id_zavodu`, `zav`.`nazev` `nazev_zavodu`, `zav`.`typ` `typ`, `zav`.`kategorie` `kategorie_zavodu`, `zz`.`tym`, `cips1`, `umisteni1`, `cips2`, `umisteni2`
					FROM `zavodnici_zavody` `zz`
					JOIN `zavodnici_kategorie` `zk` ON `zz`.`id_zavodnika` = `zk`.`id_zavodnika`
					JOIN `zavody` `zav` ON `zz`.`id_zavodu` = `zav`.`id`
					WHERE `zk`.`rok` = `zav`.`rok` 
					AND (`cips1` IS NOT NULL OR `cips2` IS NOT NULL)
					AND (`zav`.`zobrazovat` = 'ano')
					AND (`zav`.`vysledky` = 'ano')
					AND `zz`.`id_zavodnika` = ? AND `zav`.`rok` = ?";

		if ($omezeni === null)
			$query .= " AND `zav`.`kategorie` = ''";
		if ($omezeni === 'ženy')
			$query .= " AND `zk`.`kategorie` IN ('u10_zena', 'u14_zena', 'u18_zena', 'u23_zena', 'zena', 'u12_zena', 'u15_zena', 'u20_zena', 'u25_zena') AND (`zav`.`kategorie` = '' OR `zav`.`kategorie` = 'zeny')";
		if ($omezeni[0] === 'u')
			$query .= " AND `zk`.`kategorie` IN ('" . $omezeni . "', '" . $omezeni . "_zena') AND `zav`.`kategorie` != 'zeny'";

		$query .= " ORDER BY `zav`.`datum_od`, `zav`.`nazev`";

		$result = $this->database->query($query, $competitorId, $rok);

		foreach ($result as $row) {
			$competitionResults[$row->id_zavodu] = ['nazev_zavodu' => $row->nazev_zavodu, 'tym' => $row['tym'], 'typ_zavodu' => $row->typ, 'kategorie_zavodu' => $row->kategorie_zavodu, 'id_zavodu' => $row->id_zavodu, 'umisteni1' => $row->umisteni1, 'umisteni2' => $row->umisteni2, 'cips1' => $row->cips1, 'cips2' => $row->cips2];
			if ($row->umisteni1 !== null)
				$competitionCount++;
			if ($row->umisteni2 !== null)
				$competitionCount++;
		}

		foreach ($competitionResults as $k => $v) {
			$typZavodu = $v['typ_zavodu'];
			$body1 = $this->getPoints($typZavodu, $v['umisteni1']);
			$body2 = $this->getPoints($typZavodu, $v['umisteni2']);
			if ($v['umisteni1'] !== null) {
				$competitionResults[$k]['body1'] = $body1;
				$competitionResults[$k]['body1_zebricek'] = false;
				$competitionResults[$k]['cips1'] = $v['cips1'];
				$totalPoints[] = $body1;
				$results['cips_celkem'][] = $v['cips1'];
			} else {
				$competitionResults[$k]['body1'] = null;
				$competitionResults[$k]['body1_zebricek'] = false;
			}
			if ($v['umisteni2'] !== null) {
				$competitionResults[$k]['body2'] = $body2;
				$competitionResults[$k]['cips2'] = $v['cips2'];
				$competitionResults[$k]['body2_zebricek'] = false;
				$totalPoints[] = $body2;
				$results['cips_celkem'][] = $v['cips2'];
			} else {
				$competitionResults[$k]['body2'] = null;
				$competitionResults[$k]['body2_zebricek'] = false;
			}
		}
		$results['body_zebricek'] = $this->getTopValuesFromArray($totalPoints, $countedCompetitions);

		$bodyZebricekKopie = $results['body_zebricek'];
		foreach ($competitionResults as $k => $v) {
			$body1 = $competitionResults[$k]['body1'];
			if ($body1 !== null) {
				$ind = \array_search($body1, $bodyZebricekKopie, true);
				if ($ind !== false) {
					$competitionResults[$k]['body1_zebricek'] = true;
					unset($bodyZebricekKopie[$ind]);
				}
			}
			$body2 = $competitionResults[$k]['body2'];
			if ($body2 !== null) {
				$ind = \array_search($body2, $bodyZebricekKopie, true);
				if ($ind !== false) {
					$competitionResults[$k]['body2_zebricek'] = true;
					unset($bodyZebricekKopie[$ind]);
				}
			}
		}

		return ['zavodu' => $competitionCount, 'body_celkem' => $totalPoints, 'vysledky' => $competitionResults];
	}

	private function getTopValuesFromArray(array $input, int $count): array
	{
		if (\count($input) > $count) {
			rsort($input);
		}
		return \array_slice($input, 0, $count);
	}

	private function bodySort($a, $b): int
	{
		$sumA = \array_sum($a['body_zebricek']);
		$sumB = \array_sum($b['body_zebricek']);
		$sumCips1 = \array_sum($a['cips_celkem']);
		$sumCips2 = \array_sum($b['cips_celkem']);

		if ($sumA == $sumB) {
			if ($a['zavodu'] < $b['zavodu'])
				return -1;
			else if ($a['zavodu'] > $b['zavodu'])
				return 1;
			else {
				return -($sumCips1 <=> $sumCips2);
			}
		} else if ($sumA > $sumB) {
			return -1;
		} else {
			return 1;
		}
	}

}
