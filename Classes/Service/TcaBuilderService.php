<?php

namespace Buepro\Easyconf\Service;

use Buepro\Easyconf\Mapper\EasyconfMapper;
use Buepro\Easyconf\Mapper\TypoScriptConstantMapper;
use Buepro\Easyconf\Utility\PropertyHelper;
use Buepro\Easyconf\Utility\TcaUtility;
use Random\RandomException;
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

    function createType($type): Type
    {
        $type = new Type($this, $type);
        $this->types[$type->getType()] = $type;
        return $type;
    }

    static public function getConstantDefaultFilePath($constantDefaultFilename = 'constant_default.typoscript'): string
    {
        return Environment::getConfigPath() . '/' . $constantDefaultFilename;
    }

    function generateColumnData(): void
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


    function setClearCache()
    {

    }

    /**
     * @return void
     */
    function generateConstantFile(): void
    {
        $content = '';
        foreach ($this->propertyMap as $property) {
            if($property['mapper'] === TypoScriptConstantMapper::class) {
                foreach ($property['fieldPropertyMap'] as $field => $propertyName) {
                    if($property['fieldModifyMap'][$field]['defaultReference'] ?? false) {
                        $content .= "{$property['path']}.{$propertyName} < " . $property['fieldModifyMap'][$field]['defaultReference'] . "\n";
                    } else {
                        $content .= "{$property['path']}.{$propertyName} = " . ($property['fieldModifyMap'][$field]['default'] ?? "") . "\n";
                    }
                }
            }
        }
        GeneralUtility::writeFile($this->getConstantDefaultFilePath(), $content);
    }

    /**
     * @return void
     */
    public static function includeConstantDefaultFileContent(): void
    {
        if(@file_exists(self::getConstantDefaultFilePath())) {
            ExtensionManagementUtility::addTypoScriptConstants(
                "@import '" . Environment::getConfigPath() . "/constant_default.typoscript'"
            );
        }
    }
}
