<?php

namespace App\Model;

use App\Exceptions\CategoryNotFoundException;
use App\Exceptions\CompetitorNotFoundException;
use App\Exceptions\UnknownCategoryException;
use App\Exceptions\UnregisteredCompetitorNotFoundException;

final class CheckedResults
{

	/** @var Competitors */
	private $competitors;

	public function __construct(Competitors $competitors)
	{
		$this->competitors = $competitors;
	}

	/**
	 * @return CheckedResultRow[]
	 */
	public function check(array $results, int $year, array $allowedCategories): array
	{
		$result = [];
		foreach ($results as $id => $r) {
			try {
				$category = Category::fromString($r['kategorie']);
				$r['kategorie'] = $category;
				$hasCategory = true;
			} catch (UnknownCategoryException $exc) {
				$hasCategory = false;
				$category = null;
			}
			$differingName = null;
			$differingCategory = null;
			if (preg_match('~^(\d+)$~', $r['registrace'], $m)) {
				// registrovany zavodnik
				try {
					$competitor = $this->competitors->getByRegistration($r['registrace']);
					if ($this->checkNames($r['prijmeni'], $competitor->getFullName())) {
						$differingName = $competitor->getFullName();
					}
					try {
						$competitorCategory = $this->competitors->getCategoryByCompetitor($competitor->getId(), $year);
						if ($category !== null && $category->getCategory() === $competitorCategory->getCategory()) {
							$status = CheckStatus::NO_PROBLEM;
						} else {
							$differingCategory = $competitorCategory;
							$status = CheckStatus::CATEGORIES_DIFFER;
						}
					} catch (CategoryNotFoundException $exc) {
						$status = $hasCategory ? CheckStatus::ADD_CATEGORY : CheckStatus::NO_CATEGORY_SPECIFIED;
					}
				} catch (CompetitorNotFoundException $exc) {
					$status = $hasCategory ? CheckStatus::NEW : CheckStatus::NO_CATEGORY_SPECIFIED;
				}
			} else {
				// neregistrovany zavodnik
				try {
					$zavodnik = $this->competitors->getUnregisteredIdByName($r['prijmeni']);
					$competitorCategory = $this->competitors->getCategoryByCompetitor($zavodnik, $year);
					if ($category !== null && $category->getCategory() === $competitorCategory->getCategory()) {
						$status = CheckStatus::NO_PROBLEM;
					} else {
						$differingCategory = $competitorCategory;
						$status = CheckStatus::CATEGORIES_DIFFER;
					}
				} catch (UnregisteredCompetitorNotFoundException $exc) {
					$status = $hasCategory ? CheckStatus::NEW : CheckStatus::NO_CATEGORY_SPECIFIED;
				} catch (CategoryNotFoundException $exc) {
					$status = $hasCategory ? CheckStatus::NEW : CheckStatus::NO_CATEGORY_SPECIFIED;
				}
			}
			if ($hasCategory && $category !== null && !isset($allowedCategories[$category->getCategory()])) {
				$status = CheckStatus::CATEGORY_NOT_ALLOWED;
			}
			$checkedResult = new CheckedResultRow($r['registrace'], $r['prijmeni'], $r['tym'], $category, new CheckStatus($status, $differingName !== null));
			if ($differingCategory !== null) {
				$checkedResult->setDifferingCategory($differingCategory);
			}
			if ($differingName !== null) {
				$checkedResult->setDifferingName($differingName);
			}

			if ($r['cips1'] !== '' && $r['cips1'] !== null && $r['umisteni1'] !== '' && $r['umisteni1'] !== null) {
				$round1 = new RoundResult(1, (int)$r['cips1'], (float)str_replace(',', '.', $r['umisteni1']));
				$checkedResult->addRoundResult($round1);
			}

			if ($r['cips2'] !== '' && $r['cips2'] !== null && $r['umisteni2'] !== '' && $r['umisteni2'] !== null) {
				$round2 = new RoundResult(2, (int)$r['cips2'], (float)str_replace(',', '.', $r['umisteni2']));
				$checkedResult->addRoundResult($round2);
			}

			$result[$id] = $checkedResult;
		}
		return $result;
	}

	private function checkNames(string $fromInput, string $fromEntity): bool
	{
		$replacements = ['dr.', 'ml.', 'ing.', 'dis.', 'st.', 'bc.'];
		$entityName = $this->trimUnicode(\str_replace($replacements, '', \mb_strtolower(\str_replace('  ', ' ', $fromEntity))));
		$inputName = $this->trimUnicode(\str_replace($replacements, '', \mb_strtolower(\str_replace('  ', ' ', $fromInput))));

		$eEntityName = \preg_split('~\s+~', $entityName);
		$eInputName = \preg_split('~\s+~', $inputName);

		$diff = \array_diff($eInputName, $eEntityName);
		return \count($diff) > 0;
	}

	private function trimUnicode(string $input): string
	{
		return preg_replace('~^[\pZ\pC]+|[\pZ\pC]+$~u', '', $input);
	}


}