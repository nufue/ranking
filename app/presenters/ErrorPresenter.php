<?php

namespace App\Presenters;

use Nette\Application\BadRequestException;
use Tracy\Debugger;

final class ErrorPresenter extends BasePresenter
{

	public function renderDefault(\Exception $exception): void
	{
		if ($this->isAjax()) {
			$this->payload->error = true;
			$this->terminate();
		} elseif ($exception instanceof BadRequestException) {
			$code = $exception->getCode();
			$this->setView(in_array($code, [403, 404, 405, 410, 500]) ? $code : '4xx');
			Debugger::log("HTTP code $code: {$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}", 'access');
		} else {
			$this->setView('500');
			Debugger::log($exception, Debugger::ERROR);
		}
	}

}
