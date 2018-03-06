<?php

namespace App\Forms;

use Nextras\Forms\Controls\DatePicker;

final class Form extends \Nette\Application\UI\Form {

	public function addDatePicker(string $name, $label = null): DatePicker {
		return $this[$name] = new DatePicker($label);
	}

}