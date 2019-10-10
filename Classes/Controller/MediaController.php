<?php

namespace Sitegeist\ResponsiveImages\Controller;

use Sitegeist\ResponsiveImages\Domain\Repository\PageRepository;

class MediaController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * Page Repository
     *
     * @var PageRepository
     */
    protected $pageRepository;

    public function injectPageRepository(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    /**
     * Header Action
     *
     * @param int  $demo
     *
     * @return void
     */
    public function headerAction($demo = null)
    {
        if ($demo < 0) {
            $this->redirect('testing');
        }

        $this->view->assignMultiple([
            'demo' => $demo,
            'page' => $this->pageRepository->findByUid($GLOBALS['TSFE']->id)
        ]);
    }

    /**
     * Testing Action
     *
     * @return void
     */
    public function testingAction()
    {
        $this->view->assign('page', $this->pageRepository->findByUid($GLOBALS['TSFE']->id));
    }
}
