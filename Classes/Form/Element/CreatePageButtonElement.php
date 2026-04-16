<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Form\Element;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
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
        $newPageType = $this->getValue('newPageType', 'int');
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

        $result['html'] = implode(PHP_EOL, $html);

        $result['javaScriptModules'][] = JavaScriptModuleInstruction::create(
            '@buepro/easyconf/form-engine/element/create-page-button.js'
        )->instance('#' . $buttonId, [
            'targetPid' => $targetPid,
            'newPidSettingPath' => $newPidSettingPath,
            'copySourcePid' => $copySourcePid,
            'newPageTitle' => $newPageTitle,
            'newPageType' => $newPageType
        ]);
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
