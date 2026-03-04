<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Service;

use Buepro\Easyconf\Mapper\EasyconfMapper;
use Buepro\Easyconf\Mapper\TypoScriptConstantMapper;
use Buepro\Easyconf\Utility\TcaUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TcaBuilderService
{
    protected string $l10nFile = '';

    protected array $propertyMap = [];

    protected array $types = [];

    public function init(string $l10nFile): void
    {
        $GLOBALS['TCA']['tx_easyconf_configuration']['types'] ??= [];
        $GLOBALS['TCA']['tx_easyconf_configuration']['palettes'] ??= [];
        $GLOBALS['TCA']['tx_easyconf_configuration']['columns'] ??= [];
        $GLOBALS['TCA']['tx_easyconf_configuration']['ctrl']['type'] = 'group';
        $GLOBALS['TCA']['tx_easyconf_configuration']['ctrl']['cardView'] = true;

        $this->l10nFile = $l10nFile;
    }

    public function getL10nFile(): string
    {
        return $this->l10nFile;
    }

    public function addToPropertyMap(array $value): void
    {
        $this->propertyMap[] = $value;
    }

    public function createType(int $type): Type
    {
        $type = new Type($this, $type);
        $this->types[$type->getType()] = $type;
        return $type;
    }

    public static function getConstantDefaultFilePath(string $constantDefaultFilename = 'constant_default.typoscript'): string
    {
        return Environment::getConfigPath() . '/' . $constantDefaultFilename;
    }

    public function generateColumnData(): void
    {

        $GLOBALS['TCA']['tx_easyconf_configuration']['columns'] = array_merge(
            ['group' => [
                'label' => 'group',
                'tx_easyconf' => [
                    'mapper' => EasyconfMapper::class,
                    'path' => 'group.group',
                ],
                'config' => [
                    'type' => 'input'
                ]

            ]],
            TcaUtility::getColumns($this->propertyMap, $this->l10nFile)
        );
    }

    public function setClearCache(): void
    {

    }

    /**
     * @return void
     */
    public function generateConstantFile(): void
    {
        $content = '';
        foreach ($this->propertyMap as $property) {
            if ($property['mapper'] === TypoScriptConstantMapper::class) {
                foreach ($property['fieldPropertyMap'] as $field => $propertyName) {
                    if ($property['fieldModifyMap'][$field]['defaultReference'] ?? false) {
                        $content .= "{$property['path']}.{$propertyName} < " . $property['fieldModifyMap'][$field]['defaultReference'] . "\n";
                    } else {
                        $content .= "{$property['path']}.{$propertyName} = " . ($property['fieldModifyMap'][$field]['default'] ?? '') . "\n";
                    }
                }
            }
        }
        GeneralUtility::writeFile(self::getConstantDefaultFilePath(), $content);
    }

    /**
     * @return void
     */
    public static function includeConstantDefaultFileContent(): void
    {
        if (@file_exists(self::getConstantDefaultFilePath())) {
            ExtensionManagementUtility::addTypoScriptConstants(
                "@import '" . Environment::getConfigPath() . "/constant_default.typoscript'"
            );
        }
    }
}
