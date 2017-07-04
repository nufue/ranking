<?php

namespace App\Extensions;

use Nette\DI\CompilerExtension;

final class DefaultYearExtension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$config = $this->loadFromFile(__DIR__ . '/../config/year.neon');
		$this->setConfig($config);

		$this->getContainerBuilder()->addDefinition($this->prefix('config'))
			->setClass('\App\Model\DefaultYear')
			->addSetup('setDefaultYear', [$config['defaultYear']]);
	}


}