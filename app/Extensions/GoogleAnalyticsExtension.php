<?php

namespace App\Extensions;

use App\Model\GoogleAnalyticsConfig;
use Nette\DI\CompilerExtension;

final class GoogleAnalyticsExtension extends CompilerExtension
{

	public function loadConfiguration(): void
	{
		if (file_exists(__DIR__ . '/../config/analytics.neon')) {
			$config = $this->loadFromFile(__DIR__ . '/../config/analytics.neon');
			$this->setConfig($config);

			$this->getContainerBuilder()->addDefinition($this->prefix('analytics'))
				->setType(GoogleAnalyticsConfig::class)
				->addSetup('setEnabled', [$config['enabled']])
				->addSetup('setCode', [$config['code']]);
		} else {
			$this->getContainerBuilder()->addDefinition($this->prefix('analytics'))
				->setType(GoogleAnalyticsConfig::class);
		}
	}

}