<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Form\Element;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;

class BlankElement extends AbstractFormElement
{

    /**
     * @inheritDoc
     */
    public function render(): array
    {
        return ['html' => '&nbsp;', 'labelHasBeenHandled' => true];
    }
}
