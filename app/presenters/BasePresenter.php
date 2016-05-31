<?php

namespace App\Presenters;

abstract class BasePresenter extends \Nette\Application\UI\Presenter {

	/** @var int */
	protected static $defaultYear = 2016;

	public function startup() {
		parent::startup();
		$this->template->isLoggedIn = $this->getUser()->isLoggedIn();
		$userName = '';
		if ($this->getUser()->getIdentity() !== null) $userName = $this->getUser()->getIdentity()->getId(); 
		
		$this->template->userName = $userName;

		$bcStorage = $this->getSession('breadcrumb');
		if ($bcStorage->breadcrumb === NULL) {
			$bcStorage->breadcrumb = [];
		}

		$this->template->backlink = '';
		$httpRequest = $this->getHttpRequest();
		$absoluteUrl = $httpRequest->getUrl()->getAbsoluteUrl();
		$deleted = FALSE;
		if (isset($bcStorage->breadcrumb[1]) && $bcStorage->breadcrumb[1] == $absoluteUrl) {
			unset($bcStorage->breadcrumb[0]);
			unset($bcStorage->breadcrumb[1]);
			$deleted = TRUE;
		} else if (isset($bcStorage->breadcrumb[0]) && $bcStorage->breadcrumb[0] == $absoluteUrl) {
			unset($bcStorage->breadcrumb[0]);
		}


		if (count($bcStorage->breadcrumb) > 0) {
			reset($bcStorage->breadcrumb);
			$this->template->backlink = current($bcStorage->breadcrumb);
		}

		if (!$this->isAjax() && !$deleted) {
			if (in_array($absoluteUrl, $bcStorage->breadcrumb)) {
				$ind = array_search($absoluteUrl, $bcStorage->breadcrumb);
				if ($ind !== FALSE)
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
