<?php

namespace App\Presenters;

use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{

	/** @var \App\Model\DefaultYear @inject */
	public $defaultYear;

	public function startup()
	{
		parent::startup();
		$this->template->isLoggedIn = $this->getUser()->isLoggedIn();
		$userName = '';
		if ($this->getUser()->getIdentity() !== null) $userName = $this->getUser()->getIdentity()->getId();

		$this->template->userName = $userName;

		$bcStorage = $this->getSession('breadcrumb');
		if ($bcStorage->breadcrumb === null) {
			$bcStorage->breadcrumb = [];
		}

		$this->template->backlink = '';
		$absoluteUrl = $this->getHttpRequest()->getUrl()->getAbsoluteUrl();
		$deleted = false;
		if (isset($bcStorage->breadcrumb[1]) && $bcStorage->breadcrumb[1] === $absoluteUrl) {
			unset($bcStorage->breadcrumb[0]);
			unset($bcStorage->breadcrumb[1]);
			$deleted = true;
		} else if (isset($bcStorage->breadcrumb[0]) && $bcStorage->breadcrumb[0] === $absoluteUrl) {
			unset($bcStorage->breadcrumb[0]);
		}


		if (count($bcStorage->breadcrumb) > 0) {
			reset($bcStorage->breadcrumb);
			$this->template->backlink = current($bcStorage->breadcrumb);
		}

		if (!$this->isAjax() && !$deleted) {
			if (in_array($absoluteUrl, $bcStorage->breadcrumb)) {
				$ind = array_search($absoluteUrl, $bcStorage->breadcrumb);
				if ($ind !== false)
					unset($bcStorage->breadcrumb[$ind]);
			}
			array_unshift($bcStorage->breadcrumb, $absoluteUrl);
		}
		if (count($bcStorage->breadcrumb) > 8) {
			$bcStorage->breadcrumb = array_slice($bcStorage->breadcrumb, 0, 8);
		}
		$bcStorage->breadcrumb = array_values($bcStorage->breadcrumb);
	}

}
