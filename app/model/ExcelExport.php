<?php

namespace App\Model;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class ExcelExport
{

	public function doSomething2(?\DateTimeInterface $datumPlatnosti, int $year, array $rankingResults): string {
		$spreadsheet = new Spreadsheet();
		$spreadsheet->getProperties()->setCreator('Jiří Hrazdil');

		if ($datumPlatnosti !== null) {
			$spreadsheet->getProperties()->setTitle('Průběžný žebříček LRU plavaná, aktuální k ' . $datumPlatnosti->format('j. n. Y'));
		} else {
			$spreadsheet->getProperties()->setTitle('Průběžný žebříček LRU plavaná, rok ' . $year);
		}
		if ($datumPlatnosti === null) $datumPlatnosti = new \DateTime($year . '-01-01');
		$spreadsheet->getProperties()->setDescription('Aktuální žebříček LRU plavaná je k dispozici na https://www.plavana.info/');
		$spreadsheet->setActiveSheetIndex(0);
		$sheet = $spreadsheet->getActiveSheet();
		$first = true;
		foreach ($rankingResults as $r) {
			if (!$first) {
				$sheet = $spreadsheet->createSheet();
			}
			if ($first) {
				$first = false;
			}
			$sheet->setTitle($r['sheetTitle']);
			$this->addVysledky($sheet, $r['data'], $r['title'], $datumPlatnosti, '');
		}

		$spreadsheet->setActiveSheetIndex(0);
		$writer = new Xlsx($spreadsheet);

		$tempDir = __DIR__ . '/../../temp/Excel';
		if (!file_exists($tempDir)) {
			mkdir($tempDir);
		}
		$this->deleteOldTempFiles($tempDir);
		$tn = tempnam($tempDir, 'phpxls_');
		$writer->save($tn);
		return $tn;

	}

	private function addVysledky(Worksheet $sheet, $data, string $nadpis, ?\DateTimeInterface $datumPlatnosti, string $dataFilterArg): void
	{
		$rowCnt = 1;

		$saBold = [
			'font' => [
				'bold' => true,
			],
			'alignment' => [
				'horizontal' => Alignment::HORIZONTAL_CENTER,
			],
		];
		$saRight = [
			'alignment' => [
				'horizontal' => Alignment::HORIZONTAL_RIGHT,
			],
		];

		$sheet->setCellValue('A' . $rowCnt, $nadpis);
		$sheet->getStyle('A' . $rowCnt)->getFont()->setSize(14)->setBold(true);
		$sheet->getStyle('A' . $rowCnt)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$sheet->mergeCells('A' . $rowCnt . ':I' . $rowCnt);

		$rowCnt++;
		if ($datumPlatnosti !== null) {
			$sheet->SetCellValue('A' . $rowCnt, 'platný k ' . $datumPlatnosti->format('j. n. Y'));
		}
		$sheet->getStyle('A' . $rowCnt)->getFont()->setSize(12);
		$sheet->getStyle('A' . $rowCnt)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$sheet->mergeCells('A' . $rowCnt . ':I' . $rowCnt);

		$rowCnt++;
		$sheet->SetCellValue('A' . $rowCnt, 'Aktuální žebříček je dostupný na http://www.plavana.info/');
		$sheet->getCell('A' . $rowCnt)->getHyperlink()->setUrl('http://www.plavana.info/' . (!empty($dataFilterArg) ? $dataFilterArg : '') . '?utm_source=xls&utm_medium=link&utm_campaign=zebricek' . $datumPlatnosti->format('Ymd'));
		$sheet->getStyle('A' . $rowCnt)->getFont()->setSize(12);
		$sheet->getStyle('A' . $rowCnt)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
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
		$sheet->getStyle('A' . $rowCnt . ':I' . $rowCnt)->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
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
			if (!$this->includeRowInExport($row, $dataFilterArg)) {
				continue;
			}
			$rowCnt++;
			$sheet->SetCellValue('A' . $rowCnt, $poradi);
			$sheet->SetCellValue('B' . $rowCnt, $row['registrace']);
			$sheet->SetCellValue('C' . $rowCnt, $row['jmeno']);
			if (mb_strlen($row['jmeno']) > 18) {
				$sheet->getStyle('C' . $rowCnt)->getFont()->setSize(10);
			}

			$sheet->SetCellValue('D' . $rowCnt, $row['kategorie']->toShortString());
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
		$sheet->getStyle('F2:I' . $rowCnt)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

		$saBorder = [
			'borders' => [
				'allBorders' => [
					'borderStyle' => Border::BORDER_THIN,
					'color' => ['argb' => 'FF000000'],
				],
			],
		];

		$sheet->getStyle('A1:I' . $rowCnt)->applyFromArray($saBorder);
	}

	private function includeRowInExport($radek, $argument): bool
	{
		if (empty($argument))
			return true;
		if ($argument === 'u23' && ($radek['kategorie']->toShortString() === 'U23' || $radek['kategorie']->toShortString() === 'U23Ž')) return true;
		if ($argument === 'u18' && ($radek['kategorie']->toShortString() === 'U18' || $radek['kategorie']->toShortString() === 'U18Ž')) return true;
		if ($argument === 'u14' && ($radek['kategorie']->toShortString() === 'U14' || $radek['kategorie']->toShortString() === 'U14Ž')) return true;
		if ($argument === 'u10' && ($radek['kategorie']->toShortString() === 'U10' || $radek['kategorie']->toShortString() === 'U10Ž')) return true;
		if ($argument === 'u15' && ($radek['kategorie']->toShortString() === 'U15' || $radek['kategorie']->toShortString() === 'U15Ž')) return true;
		if ($argument === 'u20' && ($radek['kategorie']->toShortString() === 'U20' || $radek['kategorie']->toShortString() === 'U20Ž')) return true;
		if ($argument === 'u25' && ($radek['kategorie']->toShortString() === 'U25' || $radek['kategorie']->toShortString() === 'U25Ž')) return true;
		if ($argument === 'u12' && ($radek['kategorie']->toShortString() === 'U12' || $radek['kategorie']->toShortString() === 'U12Ž')) return true;
		if ($argument === 'zeny' && ($radek['kategorie']->toShortString() === 'U14Ž' || $radek['kategorie']->toShortString() === 'U18Ž' || $radek['kategorie']->toShortString() === 'U23Ž' || $radek['kategorie']->toShortString() === 'U10Ž' || $radek['kategorie']->toShortString() === 'Ž' || $radek['kategorie']->toShortString() === 'U12Ž' || $radek['kategorie']->toShortString() === 'U15Ž' || $radek['kategorie']->toShortString() === 'U20Ž' || $radek['kategorie']->toShortString() === 'U25Ž')) return true;
		return false;
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


}