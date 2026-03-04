<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Buepro\Easyconf\Controller\AjaxFormController;

return [
    'easyconf_ajaxform_createpage' => [
        'path' => '/easyconf/ajaxform/create-page',
        'target' => AjaxFormController::class . '::createPage',
//        'inheritAccessFromModule' => 'my_module',
    ],
];
