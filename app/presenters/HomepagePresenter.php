<?php

namespace App\Presenters;

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class HomepagePresenter extends BasePresenter {

	/** @var \App\Model\Zebricek @inject */
	public $zebricek;
	
	/** @var \App\Model\Zavody @inject */
	public $zavody;
	
	public function actionExcelExport($rok = NULL) {
		if ($rok === NULL) $rok = self::$defaultYear;

		$zebricekCelkovy = $this->zebricek->getZebricek($rok, 'celkem' /* zadny konkretni typ */);
		$zebricek = $this->zebricek->getZebricek($rok, 'excel');
		$zebricekU23 = $this->zebricek->getZebricek($rok, 'u23');
		$zebricekU18 = $this->zebricek->getZebricek($rok, 'u18');
		$zebricekU14 = $this->zebricek->getZebricek($rok, 'u14');
		$zebricekU12 = $this->zebricek->getZebricek($rok, 'u12');
		$zebricekZeny = $this->zebricek->getZebricek($rok, 'zeny');
		
		$datumPlatnosti = $zebricekCelkovy['datum_platnosti_orig'];

		
		$objExcel = new \PHPExcel;
		$objExcel->getProperties()->setCreator("Jiří Hrazdil");
		if ($datumPlatnosti !== NULL) {
			$objExcel->getProperties()->setTitle('Průběžný žebříček LRU plavaná, aktuální k '.$datumPlatnosti->format('j. n. Y'));
		} else {
			$objExcel->getProperties()->setTitle('Průběžný žebříček LRU plavaná, rok '.$rok);
		}
		if ($datumPlatnosti === NULL) $datumPlatnosti = new DateTime('1.1.'.$rok);
		$objExcel->getProperties()->setDescription('Aktuální žebříček LRU plavaná je k dispozici na http://www.plavana.info/');
		$objExcel->setActiveSheetIndex(0);
		$sheet = $objExcel->getActiveSheet();
		$sheet->setTitle('Celkový');
		$this->addVysledky($sheet, $zebricekCelkovy['zavodnici'], 'Průběžný žebříček LRU plavaná - celkem', $datumPlatnosti, 'filterVysledky', '');


		$sheet = $objExcel->createSheet();
		$sheet->setTitle('U23');
		$this->addVysledky($sheet, $zebricekU23['zavodnici'], 'Průběžný žebříček LRU plavaná - U23', $datumPlatnosti, 'filterVysledky', 'u23');

		$sheet = $objExcel->createSheet();
		$sheet->setTitle('U18');
		$this->addVysledky($sheet, $zebricekU18['zavodnici'], 'Průběžný žebříček LRU plavaná - U18', $datumPlatnosti, 'filterVysledky', 'u18');

		$sheet = $objExcel->createSheet();
		$sheet->setTitle('U14');
		$this->addVysledky($sheet, $zebricekU14['zavodnici'], 'Průběžný žebříček LRU plavaná - U14', $datumPlatnosti, 'filterVysledky', 'u14');

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
		$tempDir = __DIR__.'/../../temp/Excel';
		if (!file_exists($tempDir)) {
			mkdir($tempDir);
		}
		$dir = opendir($tempDir);
		while ($f = readdir($dir)) {
		    if (is_file($tempDir.'/'.$f) && substr($f, 0, 7) === 'phpxls_' && filemtime($tempDir.'/'.$f) < Time() - 86400 * 10)
			unlink($tempDir.'/'.$f);
		}
		closedir($dir);
		$tn = tempnam($tempDir, 'phpxls_');
		$objWriter->save($tn);
		if ($datumPlatnosti !== NULL) {
			$name = "zebricek-'.$datumPlatnosti->format('Ymd').'.xlsx";
		} else {
			$name = "zebricek-'.$rok.'.xlsx";
		}
		$response = new \Nette\Application\Responses\FileResponse($tn, $name, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', TRUE);
		$this->sendResponse($response);
	}

	private function filterVysledky($radek, $argument) {
		if (empty($argument))
			return TRUE;
		if ($argument == 'u23' && ($radek['kategorie'] == 'U23' || $radek['kategorie'] == 'U23Ž'))return TRUE;
		if ($argument == 'u18' && ($radek['kategorie'] == 'U18' || $radek['kategorie'] == 'U18Ž'))return TRUE;
		if ($argument == 'u14' && ($radek['kategorie'] == 'U14' || $radek['kategorie'] == 'U14Ž')) return true;
		if ($argument == 'u10' && ($radek['kategorie'] == 'U10' || $radek['kategorie'] == 'U10Ž')) return true;
		if ($argument == 'u12' && ($radek['kategorie'] == 'U12' || $radek['kategorie'] == 'U12Ž')) return true;
		if ($argument == 'zeny' && ($radek['kategorie'] == 'U14Ž' || $radek['kategorie'] == 'U18Ž' || $radek['kategorie'] == 'U23Ž' || $radek['kategorie'] == 'U10Ž' || $radek['kategorie'] == 'Ž' || $radek['kategorie'] == 'U12Ž')) return true;
	}

	public function actionDefault($rok = NULL, $typ = 'celkem', $show = FALSE) {
		if ($rok === NULL) {
			$this->redirect('Homepage:default', array('rok' => self::$defaultYear, 'typ' => $typ, 'show' => $show));
		}
	}

	public function renderDefault($rok, $typ = 'celkem', $show = FALSE) {
		$this->template->aktualniRok = Date("Y");
		
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
		else if ($typ == 'u12') 
			$typZebricku = 'kadeti U12';
		else if ($typ == 'zeny')
			$typZebricku = 'ženy';

		$this->template->typ = $typ;

		$this->template->typZebricku = $typZebricku;

		$this->template->zobrazitZavody = $show;

		$zebricek = $this->zebricek->getZebricek($rok, $typ);
		$this->template->rok = $rok;
		$this->template->datum_platnosti = $zebricek['datum_platnosti'];
		$this->template->zavody = $zebricek['zavody'];
		$this->template->zavodnici = $zebricek['zavodnici'];
		
		$this->template->chybejiciVysledky = $this->zavody->getChybejiciVysledky();
	}

	private function addVysledky(&$sheet, $data, $nadpis, $datumPlatnosti, $dataFilterFunction, $dataFilterArg) {
		$rowCnt = 1;

		$saBold = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			),
		);
		$saRight = array(
			'alignment' => array(
				'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
			)
		);

		$sheet->SetCellValue('A' . $rowCnt, $nadpis);
		$sheet->getStyle('A' . $rowCnt)->getFont()->setSize(14)->setBold(true);
		$sheet->getStyle('A' . $rowCnt)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->mergeCells('A' . $rowCnt . ':I' . $rowCnt);
		
		$rowCnt++;
		if ($datumPlatnosti !== NULL) {
			$sheet->SetCellValue('A' . $rowCnt, 'platný k '.$datumPlatnosti->format('j. n. Y'));
		} 
		$sheet->getStyle('A' . $rowCnt)->getFont()->setSize(12);
		$sheet->getStyle('A' . $rowCnt)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->mergeCells('A' . $rowCnt . ':I' . $rowCnt);

		$rowCnt++;
		$sheet->SetCellValue('A' . $rowCnt, 'Aktuální žebříček je dostupný na http://www.plavana.info/');
		$sheet->getCell('A'.$rowCnt)->getHyperlink()->setUrl('http://www.plavana.info/'.(!empty($dataFilterArg) ? $dataFilterArg : '').'?utm_source=xls&utm_medium=link&utm_campaign=zebricek'.$datumPlatnosti->format('Ymd'));
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
			$sheet->SetCellValue('A'. ($rowCnt + 1), 'V tomto roce nebyly zatím přidány žádné závody, takže žebříček není možné sestavit.');
		}
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
			else if ($row['kategorie'] == 'U10 dívky') 
				$k = 'U10Ž';
			else if ($row['kategorie'] == 'U12 dívky')
				$k = 'U12Ž';
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
		$sheet->getStyle('F2:I' . $rowCnt)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$saBorder = array(
			'borders' => array(
				'allborders' => array(
					'style' => \PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('argb' => 'FF000000'),
				),
			),
		);

		$sheet->getStyle('A1:I' . $rowCnt)->applyFromArray($saBorder);
	}

}
