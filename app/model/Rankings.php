<?php

namespace App\Model;

final class Rankings {

	public function getByYear(int $year): array {
		$rankings = [];
		$rankings['celkem'] = 'celkový žebříček';
		if ($year >= 2017) {
			$rankings['u25'] = 'žebříček U25';
			$rankings['u20'] = 'žebříček U20';
			$rankings['u15'] = 'žebříček U15';
		}
		if ($year <= 2016) {
			$rankings['u23'] = 'žebříček U23';
			$rankings['u18'] = 'žebříček U18';
			$rankings['u14'] = 'žebříček U14';
		}
		if ($year > 2012 && $year <= 2016) {
			$rankings['u12'] = 'žebříček U12';
		}
		if ($year <= 2012) {
			$rankings['u10'] = 'žebříček U10';
		}
		$rankings['zeny'] = 'žebříček žen';
		if ($year >= 2017)
			$rankings['hendikepovani'] = 'žebříček hendikepovaných';
		return $rankings;
	}

	public function translate(string $type): string {
		$rankingTypes = [
			'celkem' => 'celkem',
			'u10' => 'kadeti U10',
			'u12' => 'kadeti U12',
			'u14' => 'kadeti U14',
			'u15' => 'kadeti U15',
			'u18' => 'junioři U18',
			'u20' => 'junioři U20',
			'u23' => 'junioři U23',
			'u25' => 'junioři U25',
			'zeny' => 'ženy',
			'hendikepovani' => 'hendikepovaní',
		];
		return $rankingTypes[$type];
	}

	public function toExcelTitle(string $type): string {
		$rankingTypes = [
			'celkem' => 'Celkový',
			'u10' => 'U10',
			'u12' => 'U12',
			'u14' => 'U14',
			'u15' => 'U15',
			'u18' => 'U18',
			'u20' => 'U20',
			'u23' => 'U23',
			'u25' => 'U25',
			'zeny' => 'Ženy',
			'hendikepovani' => 'Hendikepovaní',
		];
		return $rankingTypes[$type];
	}

}