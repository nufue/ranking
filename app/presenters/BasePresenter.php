<?php

/**
 * Base class for all application presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter {

	protected static $defaultYear = 2013;

	public function startup() {
		parent::startup();
		$this->template->isLoggedIn = $this->getUser()->isLoggedIn();
		$userName = '';
		if ($this->getUser()->getIdentity() !== null) $userName = $this->getUser()->getIdentity()->getId(); 
		
		$this->template->userName = $userName;

		$bcStorage = $this->context->session->getSection('breadcrumb');
		if ($bcStorage->breadcrumb === NULL) {
			$bcStorage->breadcrumb = array();
		}

		$this->template->backlink = '';
		//\Nette\Diagnostics\Debugger::dump($bcStorage->breadcrumb);
		$httpRequest = $this->context->httpRequest;
		$absoluteUrl = $httpRequest->getUrl()->getAbsoluteUrl();
		$deleted = false;
		if (isset($bcStorage->breadcrumb[1]) && $bcStorage->breadcrumb[1] == $absoluteUrl) {
			unset($bcStorage->breadcrumb[0]);
			unset($bcStorage->breadcrumb[1]);
			$deleted = true;
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
		//\Nette\Diagnostics\Debugger::dump($bcStorage->breadcrumb);
		
	}

}
