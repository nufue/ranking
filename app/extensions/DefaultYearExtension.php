<?php

namespace App\Extensions;

use Nette\DI\CompilerExtension;
use App\Model\DefaultYear;

final class DefaultYearExtension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$config = $this->loadFromFile(__DIR__ . '/../config/year.neon');
		$this->setConfig($config);

		$this->getContainerBuilder()->addDefinition($this->prefix('config'))
			->setClass(DefaultYear::class)
			->addSetup('setDefaultYear', [$config['defaultYear']]);
	}

}