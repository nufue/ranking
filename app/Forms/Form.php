<?php

namespace App\Forms;

use Nextras\Forms\Controls\DatePicker;

final class Form extends \Nette\Application\UI\Form {

	public function addDatePicker(string $name, $label = null, $cols = 10): DatePicker {
		$picker = new DatePicker($label);
		$picker->setHtmlAttribute('size', $cols);
		return $this[$name] = $picker;
	}

}