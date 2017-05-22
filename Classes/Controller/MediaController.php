<?php

namespace SMS\SmsResponsiveImages\Controller;

class MediaController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * Page Repository
     *
     * @var \SMS\SmsResponsiveImages\Domain\Repository\PageRepository
     * @inject
     */
    protected $pageRepository;

    /**
     * Header Action
     *
     * @param int  $demo
     *
     * @return void
     */
    public function headerAction($demo = null)
    {
        $this->view->assignMultiple([
            'demo' => $demo,
            'page' => $this->pageRepository->findByUid($GLOBALS['TSFE']->id)
        ]);
    }
}
