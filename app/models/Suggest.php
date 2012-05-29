<?php

class Suggest extends Base {

	public function getSuggest($s) {
		return $this->database->query("SELECT cele_jmeno, registrace FROM zavodnici WHERE cele_jmeno LIKE '".$s."%'")->fetchAll();
	}
	
}