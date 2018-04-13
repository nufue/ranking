<?php
declare(strict_types=1);

namespace App\Model;

final class CompetitorInCompetition {

	/** @var Competitor */
	private $competitor;

	/** @var Category */
	private $category;

	/** @var CompetitionResultRow */
	private $competitionResultRow;

	public function __construct(Competitor $competitor, Category $category, CompetitionResultRow $competitionResultRow)
	{
		$this->competitor = $competitor;
		$this->category = $category;
		$this->competitionResultRow = $competitionResultRow;
	}

	public function getCompetitor(): Competitor
	{
		return $this->competitor;
	}

	public function getCategory(): Category
	{
		return $this->category;
	}

	public function getCompetitionResultRow(): CompetitionResultRow
	{
		return $this->competitionResultRow;
	}

}