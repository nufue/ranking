<?php



class Zavody extends Base {

    public static $bodovaci_tabulky = array(
	    1 => array(1 => 40, 38, 36, 34, 32, 30, 28, 26, 24, 22, 20, 18, 16, 14, 12, 10, 8, 6, 4, 2, 1),
	    2 => array(1 => 36, 33, 31, 29, 27, 25, 22, 19, 16, 13, 10, 7, 4, 1),
	    3 => array(1 => 30, 27, 25, 23, 21, 19, 16, 13, 9, 6, 3, 1),
	    4 => array(1 => 25, 22, 19, 16, 13, 11, 10, 8, 5, 3, 2, 1),
	    5 => array(1 => 15, 12, 10, 8, 6, 4, 2, 1),
	);
    
    public static $typyZavodu = array(
            'memicr' => 'MeMi ČR seniorů',
            '1_liga' => '1. liga',
            '2_liga' => '2. liga',
            'bodovany_pohar' => 'Bodovaný pohárový závod',
            'uzemni_prebor' => 'Územní přebor dospělých',
            'divize' => 'Divize',
            'micr_zeny' => 'MiČR žen',
            'micr_u14' => 'MiČR U14',
            'micr_u18' => 'MiČR U18',
            'micr_u22' => 'MiČR U22',
            'zavod_zeny' => 'Závod žen',
            'zavod_u14' => 'Závod U14',
            'zavod_u18' => 'Závod U18',
            'zavod_u22' => 'Závod U22',
            'prebor_u14' => 'Územní přebor U14',
            'prebor_u18' => 'Územní přebor U18',
            'prebor_u22' => 'Územní přebor U22',
	);
    
    public static $bodovani_zavodu = array(
	    'memicr' => 1,
	    '1_liga' => 2,
	    '2_liga' => 3,
	    'bodovany_pohar' => 3,
	    'micr_zeny' => 3,
	    'micr_u14' => 3,
	    'micr_u18' => 3,
	    'micr_u22' => 3,
	    'uzemni_prebor' => 4,
	    'zavod_zeny' => 4,
	    'zavod_u14' => 4,
	    'zavod_u18' => 4,
	    'zavod_u22' => 4,
	    'divize' => 5,
	    'prebor_u14' => 5,
	    'prebor_u18' => 5,
	    'prebor_u22' => 5,
	);
    
    public function getZavody($rok) {
	$dbResult = $this->database->query($query = "SELECT * FROM zavody WHERE `rok` = ?", $rok);
	return $dbResult;
    }
    
    public function getZavod($id) {
	$dbResult = $this->database->query($query = "SELECT `id`, `nazev`, `typ`, `datum_od`, `datum_do` FROM zavody WHERE `id` = ?", $id)->fetch();
	return $dbResult;
    }
    
    


}