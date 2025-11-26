<?php


use Buepro\Easyconf\Controller\AjaxFormController;

return [
    'easyconf_ajaxform_createpage' => [
        'path' => '/easyconf/ajaxform/create-page',
        'target' => AjaxFormController::class . '::createPage',
//        'inheritAccessFromModule' => 'my_module',
    ],
];
