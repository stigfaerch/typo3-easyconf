<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Form\FieldInformation;

use Buepro\Easyconf\Mapper\TypoScriptConstantMapper;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ResetFieldButton extends AbstractNode
{

    public function render(): array
    {
        $result = [];
        /**
         * TODO
         * Add and include stylesheet
         */
        $fieldElementName = "data{$this->data['elementBaseName']}";
        $resetId = "reset_{$this->data['fieldName']}";
        $fieldConfig =& $GLOBALS['TCA']['tx_easyconf_configuration']['columns'][$this->data['fieldName']];
        $defaultValue = $fieldConfig['default'] ?? false;
        $currentValue = $this->data['parameterArray']['itemFormElValue'];
        $currentValue = is_array($currentValue) ? ($currentValue[0] ?? false) : $currentValue;
        if (($fieldConfig['tx_easyconf']['mapper'] !== TypoScriptConstantMapper::class) or (!$defaultValue && $currentValue == '')  or ($defaultValue == $currentValue)) {
            return ['html' => ''];
        }
        $iconPath = \TYPO3\CMS\Core\Utility\PathUtility::getPublicResourceWebPath('EXT:core/Resources/Public/Icons/T3Icons/svgs/actions/actions-undo.svg');
        $result['javaScriptModules'][] = JavaScriptModuleInstruction::create(
            '@buepro/easyconf/form-engine/field-wizard/reset-field-value.js'
        )->instance($fieldElementName, $resetId);

        $label = $GLOBALS['LANG']->sL('LLL:EXT:easyconf/Resources/Private/Language/locallang.xlf:fieldValue.reset.description');
        $svg = GeneralUtility::makeInstance(IconFactory::class)->getIcon('actions-undo', IconSize::SMALL)->render();
        $result['html'] = '<a href="#" title="' . htmlspecialchars($label) . '" id="' . $resetId . '" class="btn btn-default" style="margin-top: 0.5em; padding:0!important; width:24px; height:24px;">' . $svg . '</a>';
        return $result;
    }
}
