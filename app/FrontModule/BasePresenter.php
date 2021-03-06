<?php
declare(strict_types=1);

namespace App\FrontModule\Presenters;

use App\Model\DefaultYear;
use App\Model\GoogleAnalyticsConfig;
use Nette\Application\UI\Presenter;
use Nette\Http\Session;

abstract class BasePresenter extends Presenter
{

	/** @var DefaultYear @inject */
	public $defaultYear;

	/** @var GoogleAnalyticsConfig @inject */
	public $googleAnalyticsConfig;

	/** @var int @persistent */
	public $year;

	public function startup(): void
	{
		parent::startup();
		if ($this->getParameter('year') === null) {
			$params = $this->getParameters();
			$params['year'] = $this->defaultYear->getDefaultYear();
			$this->redirect('this', $params);
		}
		$this->template->currentYear = (int)$this->getParameter('year');
		$this->template->analyticsEnabled = $this->googleAnalyticsConfig->isEnabled();
		$this->template->analyticsCode = $this->googleAnalyticsConfig->getCode();

		$this->template->isLoggedIn = $this->getUser()->isLoggedIn();
		$this->template->isAdmin = $this->getUser()->isInRole('admin');
		$userName = '';
		if ($this->getUser()->getIdentity() !== null) {
			$userName = $this->getUser()->getIdentity()->getId();
		}

		$this->template->userName = $userName;

		/** @var Session $session */
		$session = $this->getSession();
		$bcStorage = $session->getSection('breadcrumb');
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

		if (\count($bcStorage->breadcrumb) > 0) {
			reset($bcStorage->breadcrumb);
			$this->template->backlink = current($bcStorage->breadcrumb);
		}

		if (!$this->isAjax() && !$deleted) {
			if (\in_array($absoluteUrl, $bcStorage->breadcrumb, true)) {
				$ind = \array_search($absoluteUrl, $bcStorage->breadcrumb, true);
				if ($ind !== false)
					unset($bcStorage->breadcrumb[$ind]);
			}
			\array_unshift($bcStorage->breadcrumb, $absoluteUrl);
		}
		if (\count($bcStorage->breadcrumb) > 8) {
			$bcStorage->breadcrumb = \array_slice($bcStorage->breadcrumb, 0, 8);
		}
		$bcStorage->breadcrumb = \array_values($bcStorage->breadcrumb);
	}

	public function handlePrev()
	{
		--$this->year;
		$params = $this->getParameters();
		$params['year'] = $this->year;
		unset($params['do']);
		$this->redirect('this', $params);
	}

	public function handleNext()
	{
		++$this->year;
		$params = $this->getParameters();
		$params['year'] = $this->year;
		unset($params['do']);
		$this->redirect('this', $params);
	}

}
