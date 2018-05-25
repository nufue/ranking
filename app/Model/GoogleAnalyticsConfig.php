<?php

namespace App\Model;


final class GoogleAnalyticsConfig
{
	/** @var bool */
	private $enabled = false;
	/** @var string */
	private $code = '';

	public function isEnabled(): bool
	{
		return $this->enabled;
	}

	public function setEnabled(bool $enabled): void
	{
		$this->enabled = $enabled;
	}

	public function getCode(): string
	{
		return $this->code;
	}

	public function setCode(string $code): void
	{
		$this->code = $code;
	}

}