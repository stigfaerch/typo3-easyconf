<?php

namespace Buepro\Easyconf\Form\FieldInformation;

use Buepro\Easyconf\Mapper\TypoScriptConstantMapper;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ResetFieldButton extends AbstractNode
{

    public function render()
    {
        /**
         * TODO
         * Add and include stylesheet
         */
        $fieldElementName = "data{$this->data['elementBaseName']}";
        $resetId = "reset_{$this->data['fieldName']}";
        $fieldConfig =& $GLOBALS['TCA']['tx_easyconf_configuration']['columns'][$this->data['fieldName']];
        $defaultValue = $fieldConfig['default'] ?? false;
        $currentValue = $this->data['parameterArray']['itemFormElValue'];
        if (($fieldConfig['tx_easyconf']['mapper'] !== TypoScriptConstantMapper::class) OR (!$defaultValue && $currentValue == '')  OR ($defaultValue == $currentValue)) {
            return ['html' => ''];
        }
        $iconPath = \TYPO3\CMS\Core\Utility\PathUtility::getPublicResourceWebPath('EXT:core/Resources/Public/Icons/T3Icons/svgs/actions/actions-undo.svg');
        $result['javaScriptModules'][] = JavaScriptModuleInstruction::create(
            '@buepro/easyconf/form-engine/field-wizard/reset-field-value.js'
        )->instance($fieldElementName, $resetId);
        $result['html'] = '<a href="#" title="Klik for at gendanne oprindelig værdi" id="' . $resetId . '" class="btn btn-default" style="margin-top: 0.5em; padding:0!important; width:24px; height:24px; background-repeat: no-repeat; background-size: 80%; background-position: center; background-image: url(\'' . $iconPath . '\')"></a>';
        return $result;
    }
}
