import AjaxRequest from '@typo3/core/ajax/ajax-request.js';

class CreatePageButton {
    constructor(buttonSelector, options) {
        const btn = document.querySelector(buttonSelector);
        if (!btn) {
            return;
        }
        btn.addEventListener('click', (e) => {
            e.preventDefault();

            const originalText = btn.innerText;
            btn.innerText = 'Arbejder...';
            btn.disabled = true;

            const data = {
                targetPid: options.targetPid,
                newPidSettingPath: options.newPidSettingPath,
                copySourcePid: options.copySourcePid,
                newPageTitle: options.newPageTitle,
            };

            new AjaxRequest(TYPO3.settings.ajaxUrls.easyconf_ajaxform_createpage)
                .post(data)
                .then(async (response) => {
                    const json = await response.resolve();
                    if (top.TYPO3?.Notification) {
                        top.TYPO3.Notification.success('Succes', 'Siden blev oprettet.');
                    }
                    return json;
                })
                .catch((err) => {
                    console.error('Fejl:', err);
                    if (top.TYPO3?.Notification) {
                        top.TYPO3.Notification.error('Fejl', 'Der skete en fejl under oprettelsen.');
                    }
                })
                .finally(() => {
                    btn.innerText = originalText;
                    btn.disabled = false;
                    window.location.reload();
                });
        });
    }
}

export default CreatePageButton;
