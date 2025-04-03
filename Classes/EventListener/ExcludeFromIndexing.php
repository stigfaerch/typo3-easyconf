<?php

namespace Buepro\Easyconf\EventListener;

use TYPO3\CMS\Core\DataHandling\Event\IsTableExcludedFromReferenceIndexEvent;

class ExcludeFromIndexing
{
    public function __invoke(IsTableExcludedFromReferenceIndexEvent $event): IsTableExcludedFromReferenceIndexEvent
    {
        if($event->getTable() == 'tx_easyconf_configuration') {
            $event->markAsExcluded();
        }
        return $event;
    }

}
