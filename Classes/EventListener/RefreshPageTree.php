<?php

namespace Buepro\Easyconf\EventListener;

use TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RefreshPageTree
{
    public function __invoke(ModifyPageLayoutContentEvent $event): ModifyPageLayoutContentEvent
    {
        if(GeneralUtility::makeInstance(Registry::class)->get('easyconf_pagetree', 'update', false)) {
            GeneralUtility::makeInstance(Registry::class)->remove('easyconf_pagetree', 'update');
            $event->addFooterContent('<script>top.document.dispatchEvent(new CustomEvent("typo3:pagetree:refresh"))</script>');
        }
        return $event;
    }
}
