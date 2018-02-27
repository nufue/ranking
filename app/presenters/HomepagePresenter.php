<?php

namespace App\Presenters;

use App\Model\Competitions;
use App\Model\Ranking;
use Nette\Application\Responses\FileResponse;

final class HomepagePresenter extends BasePresenter
{

	/** @var \App\Model\Ranking */
	private $ranking;

	/** @var \App\Model\Competitions */
	private $competitions;

	public function __construct(Ranking $ranking, Competitions $competitions)
	{
		parent::__construct();
		$this->ranking = $ranking;
		$this->competitions = $competitions;
	}

	public function handleExcelExport($rok = null): void
	{
		if ($rok === null) $rok = $this->defaultYear->getDefaultYear();

		$zebricekCelkovy = $this->ranking->getRanking($rok, 'celkem' /* zadny konkretni typ */);
		$zebricek = $this->ranking->getRanking($rok, 'excel');
		$zebricekU25 = $this->ranking->getRanking($rok, 'u25');
		$zebricekU23 = $this->ranking->getRanking($rok, 'u23');
		$zebricekU20 = $this->ranking->getRanking($rok, 'u20');
		$zebricekU18 = $this->ranking->getRanking($rok, 'u18');
		$zebricekU15 = $this->ranking->getRanking($rok, 'u15');
		$zebricekU14 = $this->ranking->getRanking($rok, 'u14');
		$zebricekU12 = $this->ranking->getRanking($rok, 'u12');
		$zebricekZeny = $this->ranking->getRanking($rok, 'zeny');

		$datumPlatnosti = $zebricekCelkovy['datum_platnosti_orig'];

		$objExcel = new \PHPExcel;
		$objExcel->getProperties()->setCreator("Jiří Hrazdil");
		if ($datumPlatnosti !== null) {
			$objExcel->getProperties()->setTitle('Průběžný žebříček LRU plavaná, aktuální k ' . $datumPlatnosti->format('j. n. Y'));
		} else {
			$objExcel->getProperties()->setTitle('Průběžný žebříček LRU plavaná, rok ' . $rok);
		}
		if ($datumPlatnosti === null) $datumPlatnosti = new DateTime('1.1.' . $rok);
		$objExcel->getProperties()->setDescription('Aktuální žebříček LRU plavaná je k dispozici na https://www.plavana.info/');
		$objExcel->setActiveSheetIndex(0);
		$sheet = $objExcel->getActiveSheet();
		$sheet->setTitle('Celkový');
		$this->addVysledky($sheet, $zebricekCelkovy['zavodnici'], 'Průběžný žebříček LRU plavaná - celkem', $datumPlatnosti, 'filterVysledky', '');

		if ($rok >= 2017) {
			$sheet = $objExcel->createSheet();
			$sheet->setTitle('U25');
			$this->addVysledky($sheet, $zebricekU25['zavodnici'], 'Průběžný žebříček LRU plavaná - U25', $datumPlatnosti, 'filterVysledky', 'u25');

			$sheet = $objExcel->createSheet();
			$sheet->setTitle('U20');
			$this->addVysledky($sheet, $zebricekU20['zavodnici'], 'Průběžný žebříček LRU plavaná - U20', $datumPlatnosti, 'filterVysledky', 'u20');

			$sheet = $objExcel->createSheet();
			$sheet->setTitle('U15');
			$this->addVysledky($sheet, $zebricekU15['zavodnici'], 'Průběžný žebříček LRU plavaná - U15', $datumPlatnosti, 'filterVysledky', 'u15');
		}
		if ($rok <= 2016) {
			$sheet = $objExcel->createSheet();
			$sheet->setTitle('U23');
			$this->addVysledky($sheet, $zebricekU23['zavodnici'], 'Průběžný žebříček LRU plavaná - U23', $datumPlatnosti, 'filterVysledky', 'u23');

			$sheet = $objExcel->createSheet();
			$sheet->setTitle('U18');
			$this->addVysledky($sheet, $zebricekU18['zavodnici'], 'Průběžný žebříček LRU plavaná - U18', $datumPlatnosti, 'filterVysledky', 'u18');

			$sheet = $objExcel->createSheet();
			$sheet->setTitle('U14');
			$this->addVysledky($sheet, $zebricekU14['zavodnici'], 'Průběžný žebříček LRU plavaná - U14', $datumPlatnosti, 'filterVysledky', 'u14');
		}

		if ($rok >= 2013) {
			$sheet = $objExcel->createSheet();
			$sheet->setTitle('U12');
			$this->addVysledky($sheet, $zebricekU12['zavodnici'], 'Průběžný žebříček LRU plavaná - U12', $datumPlatnosti, 'filterVysledky', 'u12');
		}

		if ($rok <= 2012) {
			$sheet = $objExcel->createSheet();
			$sheet->setTitle('U10');
			$this->addVysledky($sheet, $zebricek['zavodnici'], 'Průběžný žebříček LRU plavaná - U10', $datumPlatnosti, 'filterVysledky', 'u10');
		}

		$sheet = $objExcel->createSheet();
		$sheet->setTitle('Ženy');
		$this->addVysledky($sheet, $zebricekZeny['zavodnici'], 'Průběžný žebříček LRU plavaná - Ženy', $datumPlatnosti, 'filterVysledky', 'zeny');

		$objExcel->setActiveSheetIndex(0);

		$objWriter = new \PHPExcel_Writer_Excel2007($objExcel);
		$tempDir = __DIR__ . '/../../temp/Excel';
		if (!file_exists($tempDir)) {
			mkdir($tempDir);
		}
		$this->deleteOldTempFiles($tempDir);
		$tn = tempnam($tempDir, 'phpxls_');
		$objWriter->save($tn);
		if ($datumPlatnosti !== null) {
			$name = "zebricek-" . $datumPlatnosti->format('Ymd') . ".xlsx";
		} else {
			$name = "zebricek-" . $rok . ".xlsx";
		}
		$response = new FileResponse($tn, $name, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', true);
		$this->sendResponse($response);
	}

	private function deleteOldTempFiles($tempDir): void
	{
		$dir = opendir($tempDir);
		while ($f = readdir($dir)) {
			if (is_file($tempDir . '/' . $f) && substr($f, 0, 7) === 'phpxls_' && filemtime($tempDir . '/' . $f) < time() - 86400 * 10)
				unlink($tempDir . '/' . $f);
		}
		closedir($dir);
	}

	private function filterVysledky($radek, $argument): bool
	{
		if (empty($argument))
			return true;
		if ($argument === 'u23' && ($radek['kategorie'] === 'U23' || $radek['kategorie'] === 'U23Ž')) return true;
		if ($argument === 'u18' && ($radek['kategorie'] === 'U18' || $radek['kategorie'] === 'U18Ž')) return true;
		if ($argument === 'u14' && ($radek['kategorie'] === 'U14' || $radek['kategorie'] === 'U14Ž')) return true;
		if ($argument === 'u10' && ($radek['kategorie'] === 'U10' || $radek['kategorie'] === 'U10Ž')) return true;
		if ($argument === 'u15' && ($radek['kategorie'] === 'U15' || $radek['kategorie'] === 'U15Ž')) return true;
		if ($argument === 'u20' && ($radek['kategorie'] === 'U20' || $radek['kategorie'] === 'U20Ž')) return true;
		if ($argument === 'u25' && ($radek['kategorie'] === 'U25' || $radek['kategorie'] === 'U25Ž')) return true;
		if ($argument === 'u12' && ($radek['kategorie'] === 'U12' || $radek['kategorie'] === 'U12Ž')) return true;
		if ($argument === 'zeny' && ($radek['kategorie'] === 'U14Ž' || $radek['kategorie'] === 'U18Ž' || $radek['kategorie'] === 'U23Ž' || $radek['kategorie'] === 'U10Ž' || $radek['kategorie'] === 'Ž' || $radek['kategorie'] === 'U12Ž' || $radek['kategorie'] === 'U15Ž' || $radek['kategorie'] === 'U20Ž' || $radek['kategorie'] === 'U25Ž')) return true;
	}

	public function actionDefault($rok = null, $typ = 'celkem', $show = false): void
	{
		if ($rok === null) {
			$this->redirect('Homepage:', ['rok' => $this->defaultYear->getDefaultYear(), 'typ' => $typ, 'show' => $show]);
		}
	}

	public function renderDefault($rok, $typ = 'celkem', $show = false): void
	{
		if ($typ === 'celkem')
			$typZebricku = 'celkem';
		else if ($typ === 'u23')
			$typZebricku = 'junioři U23';
		else if ($typ === 'u18')
			$typZebricku = 'junioři U18';
		else if ($typ === 'u14')
			$typZebricku = 'kadeti U14';
		else if ($typ === 'u10')
			$typZebricku = 'kadeti U10';
		else if ($typ === 'u12')
			$typZebricku = 'kadeti U12';
		else if ($typ === 'u15')
			$typZebricku = 'kadeti U15';
		else if ($typ === 'u20')
			$typZebricku = 'junioři U20';
		else if ($typ === 'u25')
			$typZebricku = 'junioři U25';
		else if ($typ === 'zeny')
			$typZebricku = 'ženy';
		else if ($typ === 'hendikepovani')
			$typZebricku = 'hendikepovaní';

		$rankings = [];
		$rankings['celkem'] = 'celkový žebříček';
		if ($rok >= 2017) {
			$rankings['u25'] = 'žebříček U25';
			$rankings['u20'] = 'žebříček U20';
			$rankings['u15'] = 'žebříček U15';
		}
		if ($rok <= 2016) {
			$rankings['u23'] = 'žebříček U23';
			$rankings['u18'] = 'žebříček U18';
			$rankings['u14'] = 'žebříček U14';
		}
		if ($rok > 2012 && $rok <= 2016) {
			$rankings['u12'] = 'žebříček U12';
		}
		if ($rok <= 2012) {
			$rankings['u10'] = 'žebříček U10';
		}
		$rankings['zeny'] = 'žebříček žen';
		if ($rok >= 2017)
			$rankings['hendikepovani'] = 'žebříček hendikepovaných';

		$this->getTemplate()->rankings = $rankings;

		$this->template->typ = $typ;

		$this->template->typZebricku = $typZebricku;

		$this->template->zobrazitZavody = $show;

		$zebricek = $this->ranking->getRanking($rok, $typ);
		$this->template->rok = $rok;
		$this->template->datum_platnosti = $zebricek['datum_platnosti'];
		$this->template->zavody = $zebricek['zavody'];
		$this->template->zavodnici = $zebricek['zavodnici'];

		$this->template->chybejiciVysledky = $this->competitions->loadWithMissingResults();
	}

	private function addVysledky(&$sheet, $data, $nadpis, $datumPlatnosti, $dataFilterArg): void
	{
		$rowCnt = 1;

		$saBold = [
			'font' => [
				'bold' => true,
			],
			'alignment' => [
				'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			],
		];
		$saRight = [
			'alignment' => [
				'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
			],
		];

		$sheet->SetCellValue('A' . $rowCnt, $nadpis);
		$sheet->getStyle('A' . $rowCnt)->getFont()->setSize(14)->setBold(true);
		$sheet->getStyle('A' . $rowCnt)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->mergeCells('A' . $rowCnt . ':I' . $rowCnt);

		$rowCnt++;
		if ($datumPlatnosti !== null) {
			$sheet->SetCellValue('A' . $rowCnt, 'platný k ' . $datumPlatnosti->format('j. n. Y'));
		}
		$sheet->getStyle('A' . $rowCnt)->getFont()->setSize(12);
		$sheet->getStyle('A' . $rowCnt)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->mergeCells('A' . $rowCnt . ':I' . $rowCnt);

		$rowCnt++;
		$sheet->SetCellValue('A' . $rowCnt, 'Aktuální žebříček je dostupný na http://www.plavana.info/');
		$sheet->getCell('A' . $rowCnt)->getHyperlink()->setUrl('http://www.plavana.info/' . (!empty($dataFilterArg) ? $dataFilterArg : '') . '?utm_source=xls&utm_medium=link&utm_campaign=zebricek' . $datumPlatnosti->format('Ymd'));
		$sheet->getStyle('A' . $rowCnt)->getFont()->setSize(12);
		$sheet->getStyle('A' . $rowCnt)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->mergeCells('A' . $rowCnt . ':I' . $rowCnt);


		$rowCnt++;
		$sheet->SetCellValue('A' . $rowCnt, 'Poř.');
		$sheet->SetCellValue('B' . $rowCnt, 'REG');
		$sheet->SetCellValue('C' . $rowCnt, 'Příjmení, jméno');
		$sheet->SetCellValue('D' . $rowCnt, 'KAT');
		$sheet->SetCellValue('E' . $rowCnt, 'Organizace');
		$sheet->SetCellValue('F' . $rowCnt, 'Celkem závodů');
		$sheet->SetCellValue('G' . $rowCnt, 'Celkem bodů');
		$sheet->SetCellValue('H' . $rowCnt, 'Celkem bodů do žebříčku');
		$sheet->SetCellValue('I' . $rowCnt, 'Min. body do žeb.');
		$sheet->getStyle('F' . $rowCnt . ':I' . $rowCnt)->getFont()->setSize(10);

		$poradi = 1;
		$sheet->getPageMargins()->setLeft(0.54)->setRight(0.54);
		$sheet->getStyle('A' . $rowCnt . ':I' . $rowCnt)->getAlignment()->setWrapText(true)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$sheet->getStyle('A' . $rowCnt . ':I' . $rowCnt)->getFont()->setBold(true);

		$sheet->getColumnDimension('A')->setWidth(4.57);
		$sheet->getColumnDimension('B')->setWidth(6);
		$sheet->getColumnDimension('C')->setWidth(20);
		$sheet->getColumnDimension('D')->setWidth(5.28);
		$sheet->getColumnDimension('E')->setWidth(30);
		$sheet->getColumnDimension('F')->setWidth(6.85);
		$sheet->getColumnDimension('G')->setWidth(6.85);
		$sheet->getColumnDimension('H')->setWidth(7.42);
		$sheet->getColumnDimension('I')->setWidth(7.28);

		$sheet->getRowDimension($rowCnt)->setRowHeight(45);
		$firstDataRow = $rowCnt + 1;
		if (count($data) == 0) {
			$sheet->SetCellValue('A' . ($rowCnt + 1), 'V tomto roce nebyly zatím přidány žádné závody, takže žebříček není možné sestavit.');
		}
		foreach ($data as $row) {
			if ($row['kategorie'] === 'muži')
				$k = 'M';
			else if ($row['kategorie'] === 'ženy')
				$k = 'Ž';
			else if ($row['kategorie'] === 'hendikepovaní')
				$k = 'H';
			else if ($row['kategorie'] === 'U14 ženy')
				$k = 'U14Ž';
			else if ($row['kategorie'] === 'U18 ženy')
				$k = 'U18Ž';
			else if ($row['kategorie'] === 'U23 ženy')
				$k = 'U23Ž';
			else if ($row['kategorie'] === 'U10 dívky')
				$k = 'U10Ž';
			else if ($row['kategorie'] === 'U12 dívky')
				$k = 'U12Ž';
			else if ($row['kategorie'] === 'U15 dívky')
				$k = 'U15Ž';
			else if ($row['kategorie'] === 'U20 ženy')
				$k = 'U20Ž';
			else if ($row['kategorie'] === 'U25 ženy')
				$k = 'U25Ž';
			else
				$k = $row['kategorie'];
			$row['kategorie'] = $k;
			if (!$this->filterVysledky($row, $dataFilterArg)) {
				continue;
			}
			$rowCnt++;
			$sheet->SetCellValue('A' . $rowCnt, $poradi);
			$sheet->SetCellValue('B' . $rowCnt, $row['registrace']);
			$sheet->SetCellValue('C' . $rowCnt, $row['jmeno']);
			if (mb_strlen($row['jmeno']) > 18) {
				$sheet->getStyle('C' . $rowCnt)->getFont()->setSize(10);
			}

			$sheet->SetCellValue('D' . $rowCnt, $k);
			$sheet->SetCellValue('E' . $rowCnt, $row['tym']);
			if (mb_strlen($row['tym']) > 30) {
				$sheet->getStyle('E' . $rowCnt)->getFont()->setSize(9);
			}
			$sheet->SetCellValue('F' . $rowCnt, $row['zavodu']);
			$sheet->SetCellValue('G' . $rowCnt, array_sum($row['body_celkem']));
			$sheet->SetCellValue('H' . $rowCnt, array_sum($row['body_zebricek']));
			$sheet->SetCellValue('I' . $rowCnt, $row['min_body_zebricek']);


			$poradi++;
		}

		$sheet->getStyle('A' . $firstDataRow . ':A' . $rowCnt)->applyFromArray($saBold);
		$sheet->getStyle('B' . $firstDataRow . ':B' . $rowCnt)->applyFromArray($saRight);
		$sheet->getStyle('F2:I' . $rowCnt)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$saBorder = [
			'borders' => [
				'allborders' => [
					'style' => \PHPExcel_Style_Border::BORDER_THIN,
					'color' => ['argb' => 'FF000000'],
				],
			],
		];

		$sheet->getStyle('A1:I' . $rowCnt)->applyFromArray($saBorder);
	}

}
