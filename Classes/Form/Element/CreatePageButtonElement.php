<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Form\Element;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\Exception\MissingArrayPathException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CreatePageButtonElement extends AbstractFormElement
{
    public function __construct(private readonly \TYPO3\CMS\Core\Site\SiteFinder $siteFinder) {}

    public function render(): array
    {
        $result = $this->initializeResultArray();
        $label = $this->getValue('label');
        $newPageTitle = $this->getValue('newPageTitle');
        $targetPid = (string)$this->getValue('targetPid', 'int');
        $copySourcePid = $this->getValue('copySourcePid', 'int');
        $newPidSettingPath = $this->getValue('newPidSettingPath', 'string', false);

        $buttonId = 'btn-' . uniqid();

        $html = [];
        $html[] = '<div class="form-control-wrap">';
        $html[] = '<button type="button" id="' . $buttonId . '" class="btn btn-default">';
        $html[] = htmlspecialchars((string)$label);
        $html[] = '</button>';
        $html[] = '</div>';


        $script = <<<JS
// Must be a module so import works
import AjaxRequest from '@typo3/core/ajax/ajax-request.js';

const btn = document.getElementById('{$buttonId}');
if (btn) {
    btn.addEventListener('click', function (e) {
        e.preventDefault();

        const originalText = btn.innerText;
        btn.innerText = 'Arbejder...';
        btn.disabled = true;

        const data = {
            targetPid: '{$targetPid}',
            newPidSettingPath: '{$newPidSettingPath}',
            copySourcePid: '{$copySourcePid}',
            newPageTitle: '{$newPageTitle}',
        };

        new AjaxRequest(TYPO3.settings.ajaxUrls.easyconf_ajaxform_createpage)
            .post(data)
            .then(async (response) => {
                // response.resolve() will parse JSON or throw on HTTP error
                const json = await response.resolve();
                if (top.TYPO3 && top.TYPO3.Notification) {
                    top.TYPO3.Notification.success('Succes', 'Siden blev oprettet.');
                }
                return json;
            })
            .catch((err) => {
                console.error('Fejl:', err);
                if (top.TYPO3 && top.TYPO3.Notification) {
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
JS;

        $html[] = '<script type="module">' . $script . '</script>';

        $result['html'] = implode(PHP_EOL, $html);
        return $result;
    }

    private function getValue(int|string $key, string $type = 'string', bool $convertValue = true): string|int
    {
        $value = $this->data['parameterArray']['fieldConf']['config'][$key] ?? '';
        if ($convertValue) {
            if (str_starts_with($value, 'site-settings:')) {
                $targetPidFromSetting = substr($value, strlen('site-settings:'));
                return $this->siteFinder->getSiteByPageId($this->data['effectivePid'])->getSettings()->getAllFlat()[$targetPidFromSetting] ?? false;
            } elseif (str_starts_with($value, 'site-configuration:')) {
                $targetPidFromConfiguration = substr($value, strlen('site-configuration:'));
                try {
                    $value = \TYPO3\CMS\Core\Utility\ArrayUtility::getValueByPath(GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($this->data['effectivePid'])->getConfiguration(), $targetPidFromConfiguration, '.');
                } catch (MissingArrayPathException $e) {
                    return '';
                }
            }
        }
        return match ($type) {
            'string' => !is_array($value) ? $value : '',
            'int' => is_numeric($value) ? $value : '',
            default => '',
        };
    }
}
