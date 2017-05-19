<?php

namespace SMS\SmsImageViewhelper\Controller;

class MediaController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
	/**
	 * Page Repository
	 *
	 * @var \SMS\SmsImageViewhelper\Domain\Repository\PageRepository
	 * @inject
	 */
	protected $pageRepository;

	/**
	 * Demo Action
	 *
	 * @return void
	 */
	public function demoAction()
	{
		$this->view->assign('page', $this->pageRepository->findByUid($GLOBALS['TSFE']->id));
	}
}
