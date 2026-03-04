<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\EventListener;

use TYPO3\CMS\Core\DataHandling\Event\IsTableExcludedFromReferenceIndexEvent;

class ExcludeFromIndexing
{
    public function __invoke(IsTableExcludedFromReferenceIndexEvent $event): IsTableExcludedFromReferenceIndexEvent
    {
        if ($event->getTable() == 'tx_easyconf_configuration') {
            $event->markAsExcluded();
        }
        return $event;
    }

}
