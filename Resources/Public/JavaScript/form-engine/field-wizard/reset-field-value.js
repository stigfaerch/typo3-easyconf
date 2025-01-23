/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
import DocumentService from "@typo3/core/document-service.js";
import RegularEvent from "@typo3/core/event/regular-event.js";

var States;
!function (e) {
    e.CUSTOM = "custom"
}(States || (States = {}));

class ResetFieldValue {
    constructor(e,r) {
        DocumentService.ready().then((() => {
            this.registerEventHandler(e,r)
        }))
    }

    registerEventHandler(nameAttributeToMatch, resetId) {
        const resetIdElement = document.querySelector('a#' + resetId);

        const callBack = function (e) {
            e.preventDefault();
            const inputHidden = document.createElement('input');
            inputHidden.setAttribute('name', nameAttributeToMatch);
            inputHidden.setAttribute('type', 'hidden');
            inputHidden.value = '??'

            const span = document.createElement('span');
            span.innerHTML = '&nbsp;Gem for at gennemføre gendannelse af oprindelig værdi.'

            resetIdElement.after(span);
            resetIdElement.after(inputHidden);
        }

        const oneTimeCallback = function (e) {
            callBack(e);
            clickEvent.release();
        }

        const clickEvent = new RegularEvent("click", oneTimeCallback);

        clickEvent.bindTo(resetIdElement);
    }
}

export default ResetFieldValue;
