<?php

namespace Buepro\Easyconf\Service;

use Buepro\Easyconf\Mapper\EasyconfMapper;
use Buepro\Easyconf\Mapper\TypoScriptConstantMapper;
use Buepro\Easyconf\Utility\TcaUtility;
use Random\RandomException;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TcaBuilderService
{
    const constantDefaultFilename = 'constant_default.typoscript';

    protected string $l10nFile = '';
    protected ?array $paletteHeaderTagConfig = null;

    protected array $propertyMap = [];

    public function init(string $l10nFile): void
    {
        $this->l10nFile = $l10nFile;
    }

    public function setPaletteHeaderTagConfig(string $tag, ?array $additionalAttributes = null): void
    {
        $this->paletteHeaderTagConfig = $this->getPaletteHeaderTagConfigFromArray([$tag, $additionalAttributes]);
    }

    protected function getPaletteHeaderTagConfigFromArray($config): array
    {
        $tag = $config[0] ?? $config['tag'] ?? null;
        $additionalAttributes = $config[1] ?? $config['additionalAttributes'] ?? null;
        return ['tag' => $tag, 'additionalAttributes' => $additionalAttributes];
    }


    static public function getConstantDefaultFilePath(): string
    {
        return Environment::getConfigPath() . '/' . self::constantDefaultFilename;
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
     * @param string|null $requiredBackendUserGroup
     * @param array $parts
     * @return void
     */
    function defineType(string $type, string $cardIcon = '', string $title = null, string $subtitle = null, string $description = null, string $requiredBackendUserGroup = null, $pageUidFromSiteSetting = null, array $parts = []): void {
        $GLOBALS['TCA']['tx_easyconf_configuration']['types'][$type] =
            [
                'showitem' => implode(', ', $parts),
                'title' => $title ?? ($this->l10nFile . ':type_' . $type . '_title'),
                'subtitle' => $subtitle ?? ($this->l10nFile . ':type_' . $type . '_subtitle'),
                'description' => $description ?? ($this->l10nFile . ':type_' . $type . '_description'),
                'cardIcon' => $cardIcon,
                'requiredBackendUserGroup' => $requiredBackendUserGroup ?? '',
                'pageUidFromSiteSetting' => $pageUidFromSiteSetting ?? '',
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
     * @param string|null $id
     * @param int $lineBreakPeriod
     * @param string $header
     * @param string|null $headerTag
     * @param array $parts
     * @return string
     * @throws RandomException
     */
    function insertPalette(string $id = null, int $lineBreakPeriod = 1, string $header = '', ?string $headerTag = null, array $parts = []): string {
        if(is_null($id)) { $id = bin2hex(random_bytes(5));}
        if($header) {
            $helpProp = $this->insertProperties(EasyconfMapper::class, '', $id . 'header', [
                $this->insertHelpText($id . 'header', $header, headerTag: $headerTag, propConfig: ['colClass' => 'my-0']),
            ]);
            $parts = array_merge ([$helpProp, '--linebreak--'], $parts);
        }
        $paletteConfig = TcaUtility::getPalette(implode(',', $parts), '', $lineBreakPeriod);
        if($this->paletteHeaderTagConfig || $headerTag) {
            $paletteHeaderTagConfig = ($this->paletteHeaderTagConfig ?? $this->getPaletteHeaderTagConfigFromArray($headerTag));
            $paletteConfig = array_merge($paletteConfig, ['headerTag' => $paletteHeaderTagConfig]);
        }
        $GLOBALS['TCA']['tx_easyconf_configuration']['palettes'][$id] = $paletteConfig;
        return "--palette--;;{$id}";
//        return "--palette--;{$header};{$id}";
    }

    function insertSelect(string $property,array $options, array $propConfig = []): array
    {
        $items = $this->convertOptionsToItemsArray($options);
        $config = [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => $items,
        ];
        return $this->buildType($property, $config, $propConfig);
    }

    function insertColorPicker($property, $propConfig = []): array
    {
        $config = ['type' => 'color', 'size' => 10];
        return $this->buildType($property, $config, $propConfig);
    }

    function insertLinkToFile($property, array $allowedFileExtensions = [], $propConfig = []): array
    {
        $config = ['type' => 'link','allowedTypes' => ['file'],];
        $config = array_merge($config, ['fieldInformation' => [
            'linkImagePreview' => [
                'renderType' => 'linkImagePreview',
            ]
        ]
        ]);
        if($allowedFileExtensions) {
            $config['appearance']['allowedFileExtensions'] = $allowedFileExtensions;
//            $config['appearance']['allowedExtensions'] = $allowedFileExtensions;
//            $config['appearance']['enableBrowser'] = false;
//            $config['appearance']['browserTitle'] = 'Browser Title';
        }
        return $this->buildType($property, $config, $propConfig);
    }

    function insertLinkToPage($property, $propConfig = []): array
    {
        $config = ['type' => 'link','allowedTypes' => ['page']];
        return $this->buildType($property, $config, $propConfig);
    }

    function insertInputWithValuePicker($property, array $options, $size = '10', $propConfig = []): array
    {
        $items = $this->convertOptionsToItemsArray($options, 1, 0);
        $config = [
            'type' => 'input',
            'valuePicker' => ['items' => $items],
            'size' => $size,
        ];
        return $this->buildType($property, $config, $propConfig);
    }

    function insertCheckBox(string $property, array $options = [], string|int $cols = 1, $propConfig = []): array
    {
        if(!$options) {
            $options = [1 => ''];
            $propConfig = array_replace_recursive($propConfig, ['colClass' => 'col col-sm-auto col-md-auto']);
        }
        $items = $this->convertOptionsToItemsArray($options);
        $config = [
            'type' => 'check',
            'items' => $items,
            'cols' => $cols
        ];
        return $this->buildType($property, $config, $propConfig);
    }

    function insertRadioButtons(string $property, array $options = [], array $propConfig = []): array
    {
        $items = $this->convertOptionsToItemsArray($options);
        $config = [
            'type' => 'radio',
            'items' => $items,
        ];
        return $this->buildType($property, $config, $propConfig);
    }

    /**
     * 1. Builds property map, adding data for columns array
     *    tx_easyconf
     *    config.type
     *    config.language
     * 2. Returns string for showitem
     *
     * @param string $mapper
     * @param string $path
     * @param string $fieldPrefix
     * @param array $properties
     * @return string
     */
    function insertProperties(string $mapper, string $path, string $fieldPrefix = '', array $properties = []): string {
        $fieldPrefix = GeneralUtility::camelCaseToLowerCaseUnderscored($fieldPrefix);
        $newPropertiesForPropertyMap = [];
        $newProperties = [];
        $modify = [];
        foreach ($properties as $key => $value) {
            if(is_int($key)) {
                if(is_array($value)) {
                    $newPropertiesForPropertyMap[] = $newProperties[] = $value['property'] ?? $key;
                    $modify[] = $value;
                } elseif($value = '--linebreak--') {
                    $newProperties[] = $value;
                } else {
                    $newPropertiesForPropertyMap[] = $newProperties[] = $value;
                    $modify[] = null;
                }
            } else {
                $newProperties[] = $newPropertiesForPropertyMap[] = $key;
                $value['config'] = $value['config'] ?? [];
                if($value['displayCond'] ?? false) {
                    $value['displayCond'] = $value['displayCond'];
                }
                $this->addFieldInformationConfiguration($value['config']);
                $this->addFieldWizardConfiguration($value['config']);
                $modify[] = $value;
            }
        }
        $propertyList = implode(',', $newProperties);
        $propertyListForPropertyMap = implode(',', $newPropertiesForPropertyMap);
        $this->propertyMap[] = TcaUtility::getPropertyMap((string)$mapper, $path, $propertyListForPropertyMap, $fieldPrefix, '', $modify);
        return TcaUtility::getFieldList($propertyList, $fieldPrefix);
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
    function insertConstantProperties(string $path, string $fieldPrefix = '', array $properties = []): string {
        return $this->insertProperties(TypoScriptConstantMapper::class, $path, $fieldPrefix, $properties);
    }

    function insertHelpText(string $property = null, $header = '', $text = '', ?string $headerTag = null, ?array $headerTagAttributes = null, $width = 100, $propConfig = []): array
    {
        $headerTag = $headerTag ?? $this->paletteHeaderTagConfig['tag'] ?? 'h3';
        $headerTagAttributes = $headerTagAttributes ?? $this->paletteHeaderTagConfig['attributes'] ?? ['class' => 'form-section-headline'];
        $property = $this->randomIdIfNull($property);
        $conf = [
            'property' => 'staticText_' . $property,
            'helpText' => $text,
            'helpHeader' => $header,
            'headerTag' => $headerTag,
            'headerTagAttributes' => $headerTagAttributes,
            'config' => [
                'type' => 'staticText',
            ],
        ];

        return array_replace_recursive($conf, $propConfig);
    }

    function insertBlank(): array
    {
        return [
            'property' => 'blank_' . $this->randomIdIfNull(null),
            'config' => [
                'type' => 'blank',
            ]
        ];

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

    protected function convertOptionsToItemsArray($options, $keyForKey = 'value', $keyForValue = 'label' ): array
    {
        $callback = fn(string $k, string $v): array => [$keyForKey => $k, $keyForValue => $v];
        return array_map($callback, array_keys($options), array_values($options));
    }

    protected function randomIdIfNull($id)
    {
        return is_null($id) ? bin2hex(random_bytes(5)) : $id;
    }

    protected function buildType($property, $config, $propModify, $helpText = ''): array
    {
        $config = $config ?? [];
        $this->addFieldInformationConfiguration($config);
        $this->addFieldWizardConfiguration($config);
        $propConfig = ['property' => $property, 'helpText' => $helpText, 'config' => $config];
        return array_replace_recursive($propConfig, $propModify);
    }

    protected function addFieldInformationConfiguration(&$config): void {
        if(!is_array($config)) { return;}
        $config['fieldInformation'] = array_merge(
            $config['fieldInformation'] ?? [],
            [['renderType' => 'staticText']]);
    }
    protected function addFieldWizardConfiguration(&$config): void {
        if(!is_array($config)) { return;}
        $config['fieldWizard'] = array_merge(
            $config['fieldWizard'] ?? [],
            [['renderType' => 'resetFieldButton'],]);
    }

}
