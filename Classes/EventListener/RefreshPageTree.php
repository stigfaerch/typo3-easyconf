<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\EventListener;

use TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent;

class RefreshPageTree
{
    public function __invoke(ModifyPageLayoutContentEvent $event): ModifyPageLayoutContentEvent
    {
        if ($GLOBALS['BE_USER']->uc['easyconf_pagetree_refresh'] ?? false) {
            unset($GLOBALS['BE_USER']->uc['easyconf_pagetree_refresh']);
            $GLOBALS['BE_USER']->writeUC();
            $event->addFooterContent('<script>top.document.dispatchEvent(new CustomEvent("typo3:pagetree:refresh"))</script>');
        }
        return $event;
    }
}
