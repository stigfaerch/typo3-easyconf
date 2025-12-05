<?php

namespace Buepro\Easyconf\Mapper\PostProcess;

class PageTreeRefresh
{
    public function __construct($tcaConfiguration, $parentObject, $pageId)
    {
        $GLOBALS['BE_USER']->uc['easyconf_pagetree_refresh'] = true;
        $GLOBALS['BE_USER']->writeUC();
    }
}
