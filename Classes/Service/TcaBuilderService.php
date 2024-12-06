<?php

namespace Buepro\Easyconf\Service;

use Buepro\Easyconf\Mapper\EasyconfMapper;
use Buepro\Easyconf\Mapper\TypoScriptConstantMapper;
use Buepro\Easyconf\Utility\TcaUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TcaBuilderService
{

    protected string $l10nFile;

    protected array $propertyMap = [];

    public function init(string $l10nFile): void
    {
        $this->l10nFile = $l10nFile;
    }

    function generateColumnData(&$tca): void
    {
        $tca['columns'] = array_merge(
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

    /**
     * @param string $type
     * @param string $cardIcon
     * @param string|null $title
     * @param string|null $subtitle
     * @param string|null $description
     * @param array $parts
     * @return void
     */
    function defineType(string $type, string $cardIcon = '', string $title = null, string $subtitle = null, string $description = null, array $parts = []): void {
        $GLOBALS['TCA']['tx_easyconf_configuration']['types'][$type] =
         [
            'showitem' => implode(', ', $parts),
            'title' => $title ?? ($this->l10nFile . ':type_' . $type . '_title'),
            'subtitle' => $subtitle ?? ($this->l10nFile . ':type_' . $type . '_subtitle'),
            'description' => $description ?? ($this->l10nFile . ':type_' . $type . '_description'),
            'cardIcon' => $cardIcon,
        ];
    }

    /**
     * 1. Returns string with tab info + incoming property/palette data
     *
     * @param string $id
     * @param array $parts
     * @param string|null $tabName
     * @return string
     */
    function insertTab(string $id = null, ?string $tabName = null, array $parts = []): string {
        if(is_null($id)) { $id = bin2hex(random_bytes(5));}
        return '--div--;' . ($tabName ?? $this->l10nFile . ':' . $id)  . ', ' . implode(', ', $parts);
    }

    /**
     * 1. Adds incoming properties to palettes array
     * 2. Returns string for showitem
     *
     * @param string $id
     * @param array $parts
     * @param int $lineBreakPeriod
     * @param string $title
     * @return string
     */
    function insertPalette(string $id = null, int $lineBreakPeriod = 2, string $title = '' , array $parts = []): string {
        if(is_null($id)) { $id = bin2hex(random_bytes(5));}
        $GLOBALS['TCA']['tx_easyconf_configuration']['palettes'][$id] = TcaUtility::getPalette(implode(',', $parts), '', $lineBreakPeriod);
        return "--palette--;{$title};{$id}";
    }

    /**
     * 1. Builds property map, adding data for columns array
     *    tx_easyconf
     *    config.type
     *    config.language
     * 2. Returns string for showitem
     *
     * @param string $path
     * @param string $fieldPrefix
     * @param array $properties
     * @return string
     */
    function insertConstantProperty(string $path, string $fieldPrefix = '', array $properties = []): string {
        $newProperties = [];
        $labels = [];
        foreach ($properties as $key => $value) {
            if(is_int($key)) {
               $newProperties[] = $value;
               $labels[] = null;
            } else {
                $newProperties[] = $key;
                $labels[] = $value;
            }
        }
        $propertyList = implode(',', $newProperties);
        $labelList = implode(',', $labels);
        $this->propertyMap[] = TcaUtility::getPropertyMap(TypoScriptConstantMapper::class, $path, $propertyList, $fieldPrefix, '', $labelList );
        return TcaUtility::getFieldList($propertyList, $fieldPrefix);
    }



}
