<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Mapper\PostProcess;

class PageTreeRefresh
{
    public function __construct()
    {
        $GLOBALS['BE_USER']->uc['easyconf_pagetree_refresh'] = true;
        $GLOBALS['BE_USER']->writeUC();
    }
}
