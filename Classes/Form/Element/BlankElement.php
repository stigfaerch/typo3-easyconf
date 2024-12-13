<?php

namespace Buepro\Easyconf\Form\Element;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;

class BlankElement extends AbstractFormElement
{

    /**
     * @inheritDoc
     */
    public function render()
    {
        $resultArray['html'] = '&nbsp;';
        $resultArray['labelHasBeenHandled'] = true;
        return $resultArray;
    }
}
