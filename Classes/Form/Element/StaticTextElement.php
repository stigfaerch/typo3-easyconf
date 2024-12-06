<?php

namespace Buepro\Easyconf\Form\Element;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;

class StaticTextElement extends AbstractFormElement
{

    /**
     * @inheritDoc
     */
    public function render()
    {
        $resultArray['html'] = '';
        $parameterArray = $this->data['parameterArray'] ?? [];
        if($header = $parameterArray['fieldConf']['config']['parameters']['header'] ?? false) {
            $resultArray['html'] .= "<h3>{$header}</h3>";
        }
        if($text = $parameterArray['fieldConf']['config']['parameters']['text'] ?? false) {
            $resultArray['html'] .= "<p class='col-md-6'>{$text}</p>";
        }
        $resultArray['labelHasBeenHandled'] = true;
        return $resultArray;
    }
}
