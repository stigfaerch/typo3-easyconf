<?php

namespace Buepro\Easyconf\Form\Element;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;

class StaticTextElement extends AbstractFormElement
{

    /**
     * @inheritDoc
     */
    public function render(): array
    {
        $resultArray['html'] = '';
        $parameterArray = $this->data['parameterArray'] ?? [];
        $width = $parameterArray['fieldConf']['width'] ?? 100;
        // Ensure the width is within the range of 0 to 100
        $width = max(0, min(100, $width));

        // Map the width (0-100) to the range (0-12)
        $cols = round(($width / 100) * 12);
        $config = $parameterArray['fieldConf'];
        $headerAttributes = '';
        if($config['headerAttributes'] ?? false) {
            foreach ($config['headerAttributes'] as $key => $value) {
                $headerAttributes .= ' ' . $key . '="' . $value . '"';
            }
        }
        if($header = $config['helpHeader'] ?? false) {
            $resultArray['html'] .= "<{$config['headerTag']} $headerAttributes>{$header}</{$config['headerTag']}>";
        }
        if($text = $parameterArray['fieldConf']['helpText'] ?? false) {
            $resultArray['html'] .= "<div class='col-md-{$cols}'>{$text}</div>";
        }
        $resultArray['labelHasBeenHandled'] = true;
        return $resultArray;
    }
}
