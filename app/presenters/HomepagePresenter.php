<?php

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class HomepagePresenter extends BasePresenter {

	public function actionExcelExport() {
		$rok = 2012;

		$zebricek = $this->context->zebricek->getZebricek($rok, '' /* zadny konkretni typ */);
		$datumPlatnosti = $zebricek['datum_platnosti_orig'];
		
		$objExcel = new PHPExcel;
		$objExcel->getProperties()->setCreator("Jiří Hrazdil");
		$objExcel->getProperties()->setTitle('Průběžný žebříček LRU plavaná, aktuální k '.$datumPlatnosti->format('j. n. Y'));
		$objExcel->getProperties()->setDescription('Aktuální žebříček LRU plavaná je k dispozici na http://www.plavana.info/');
		$objExcel->setActiveSheetIndex(0);
		$sheet = $objExcel->getActiveSheet();
		$sheet->setTitle('Celkový');
		$this->addVysledky($sheet, $zebricek['zavodnici'], 'Průběžný žebříček LRU plavaná - celkem', $datumPlatnosti, 'filterVysledky', '');

		$sheet = $objExcel->createSheet();
		$sheet->setTitle('U23');
		$this->addVysledky($sheet, $zebricek['zavodnici'], 'Průběžný žebříček LRU plavaná - U23', $datumPlatnosti, 'filterVysledky', 'u23');

		$sheet = $objExcel->createSheet();
		$sheet->setTitle('U18');
		$this->addVysledky($sheet, $zebricek['zavodnici'], 'Průběžný žebříček LRU plavaná - U18', $datumPlatnosti, 'filterVysledky', 'u18');

		$sheet = $objExcel->createSheet();
		$sheet->setTitle('U14');
		$this->addVysledky($sheet, $zebricek['zavodnici'], 'Průběžný žebříček LRU plavaná - U14', $datumPlatnosti, 'filterVysledky', 'u14');

		$sheet = $objExcel->createSheet();
		$sheet->setTitle('U10');
		$this->addVysledky($sheet, $zebricek['zavodnici'], 'Průběžný žebříček LRU plavaná - U10', $datumPlatnosti, 'filterVysledky', 'u10');
		
		
		$sheet = $objExcel->createSheet();
		$sheet->setTitle('Ženy');
		$this->addVysledky($sheet, $zebricek['zavodnici'], 'Průběžný žebříček LRU plavaná - Ženy', $datumPlatnosti, 'filterVysledky', 'zeny');
		
		$objExcel->setActiveSheetIndex(0);
		
		$objWriter = new PHPExcel_Writer_Excel2007($objExcel);
		$tempDir = WWW_DIR . '/../temp/Excel';
		if (!file_exists($tempDir)) {
			mkdir($tempDir);
		}
		$tn = tempnam($tempDir, 'phpxls_');
		$objWriter->save($tn);
		$response = $this->getHttpResponse();
		$response->setHeader('Content-Transfer-Encoding', "binary");
		$response->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		$response->setHeader('Content-Description', "File Transfer");
		$response->setHeader('Content-Disposition', 'attachment; filename="zebricek-'.$datumPlatnosti->format('Ymd').'.xlsx"');
		$response->setHeader('Expires', '0');
		$response->setHeader('Cache-Control', 'must-revalidate');
		$response->setHeader('Pragma', 'public');
		$response->setHeader('Content-Length', filesize($tn));
		readfile($tn);
		$this->sendResponse($response);
	}

	private function filterVysledky($radek, $argument) {
		if (empty($argument))
			return true;
		if ($argument == 'u23' && ($radek['kategorie'] == 'U23' || $radek['kategorie'] == 'U23Ž')) return true;
		if ($argument == 'u18' && ($radek['kategorie'] == 'U18' || $radek['kategorie'] == 'U18Ž')) return true;
		if ($argument == 'u14' && ($radek['kategorie'] == 'U14' || $radek['kategorie'] == 'U14Ž')) return true;
		if ($argument == 'u10' && ($radek['kategorie'] == 'U10' || $radek['kategorie'] == 'U10Ž')) return true;
		if ($argument == 'zeny' && ($radek['kategorie'] == 'U14Ž' || $radek['kategorie'] == 'U18Ž' || $radek['kategorie'] == 'U23Ž' || $radek['kategorie'] == 'U10Ž' || $radek['kategorie'] == 'Ž')) return true;
	}

	public function renderDefault($typ = 'celkem', $show = false) {
		$rok = 2012;
		
		if ($typ == 'celkem')
			$typZebricku = 'celkem';
		else if ($typ == 'u23')
			$typZebricku = 'junioři U23';
		else if ($typ == 'u18')
			$typZebricku = 'junioři U18';
		else if ($typ == 'u14')
			$typZebricku = 'kadeti U14';
		else if ($typ == 'u10') 
			$typZebricku = 'kadeti U10';
		else if ($typ == 'zeny')
			$typZebricku = 'ženy';

		$this->template->typ = $typ;

		$this->template->typZebricku = $typZebricku;

		$this->template->zobrazitZavody = $show;

		$zebricek = $this->context->zebricek->getZebricek($rok, $typ);
		$this->template->datum_platnosti = $zebricek['datum_platnosti'];
		$this->template->zavody = $zebricek['zavody'];
		$this->template->zavodnici = $zebricek['zavodnici'];
		
		$this->template->chybejiciVysledky = $this->context->zavody->getChybejiciVysledky();
	}

	private function addVysledky(&$sheet, $data, $nadpis, $datumPlatnosti, $dataFilterFunction, $dataFilterArg) {
		$rowCnt = 1;

		$saBold = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			),
		);
		$saRight = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
			)
		);

		$sheet->SetCellValue('A' . $rowCnt, $nadpis);
		$sheet->getStyle('A' . $rowCnt)->getFont()->setSize(14)->setBold(true);
		$sheet->getStyle('A' . $rowCnt)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->mergeCells('A' . $rowCnt . ':I' . $rowCnt);
		
		$rowCnt++;
		$sheet->SetCellValue('A' . $rowCnt, 'platný k '.$datumPlatnosti->format('j. n. Y'));
		$sheet->getStyle('A' . $rowCnt)->getFont()->setSize(12);
		$sheet->getStyle('A' . $rowCnt)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->mergeCells('A' . $rowCnt . ':I' . $rowCnt);

		$rowCnt++;
		$sheet->SetCellValue('A' . $rowCnt, 'Aktuální žebříček je dostupný na http://www.plavana.info/');
		$sheet->getCell('A'.$rowCnt)->getHyperlink()->setUrl('http://www.plavana.info/'.(!empty($dataFilterArg) ? $dataFilterArg : '').'?utm_source=xls&utm_medium=link&utm_campaign=zebricek'.$datumPlatnosti->format('Ymd'));
		$sheet->getStyle('A' . $rowCnt)->getFont()->setSize(12);
		$sheet->getStyle('A' . $rowCnt)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
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
		$sheet->getStyle('A' . $rowCnt . ':I' . $rowCnt)->getAlignment()->setWrapText(true)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
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
		foreach ($data as $row) {
			if ($row['kategorie'] == 'muži')
				$k = 'M';
			else if ($row['kategorie'] == 'ženy')
				$k = 'Ž';
			else if ($row['kategorie'] == 'hendikepovaní')
				$k = 'H';
			else if ($row['kategorie'] == 'U14 ženy')
				$k = 'U14Ž';
			else if ($row['kategorie'] == 'U18 ženy')
				$k = 'U18Ž';
			else if ($row['kategorie'] == 'U23 ženy')
				$k = 'U23Ž';
			else
				$k = $row['kategorie'];
			$row['kategorie'] = $k;
			if (!$this->$dataFilterFunction($row, $dataFilterArg))
				continue;
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
		$sheet->getStyle('F2:I' . $rowCnt)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$saBorder = array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('argb' => 'FF000000'),
				),
			),
		);

		$sheet->getStyle('A1:I' . $rowCnt)->applyFromArray($saBorder);
	}

}
