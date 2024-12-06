<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

defined('TYPO3') || die('Access denied.');

(static function () {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
        '@import "EXT:easyconf/Configuration/TypoScript/setup.typoscript"'
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptConstants(
        '@import "EXT:easyconf/Configuration/TypoScript/constants.typoscript"'
    );

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord']
        [\Buepro\Easyconf\DataProvider\FormDataProvider::class] = [
            'depends' => [\TYPO3\CMS\Backend\Form\FormDataProvider\TcaFlexPrepare::class],
            'before' => [\TYPO3\CMS\Backend\Form\FormDataProvider\TcaFlexProcess::class],
        ];

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']
        ['easyconf'] = \Buepro\Easyconf\Hook\DataHandlerHook::class;

    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    foreach (['Extension'] as $iconKey) {
        $iconRegistry->registerIcon(
            'easyconf-' . strtolower($iconKey),
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:easyconf/Resources/Public/Icons/' . $iconKey . '.svg']
        );
    }
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1733328550] = [
        'nodeName' => 'staticText',
        'priority' => 40,
        'class' => \Buepro\Easyconf\Form\Element\StaticTextElement::class,
    ];
})();
