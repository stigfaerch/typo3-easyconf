<?php

namespace Buepro\Easyconf\Utility;

class PropertyHelper
{
    private static ?array $headerTagConfig = [];


    static function setHeaderTagConfig(string $tag, ?array $additionalAttributes = null): void
    {
        self::$headerTagConfig = self::getHeaderTagConfigFromArray([$tag, $additionalAttributes]);
    }

    static function getHeaderTagConfig(): ?array
    {
        return self::$headerTagConfig;
    }

    static function getHeaderTagConfigFromArray($config): array
    {
        $tag = $config[0] ?? $config['tag'] ?? null;
        $additionalAttributes = $config[1] ?? $config['additionalAttributes'] ?? null;
        return ['tag' => $tag, 'additionalAttributes' => $additionalAttributes];
    }




    static function addClearCacheForSite(array $config): array
    {
        $config['clearCacheForSite'] = true;
        return $config;
    }

    static function blank(): array
    {
        return [
            'property' => 'blank_' . self::randomIdIfNull(null),
            'config' => [
                'type' => 'blank',
            ]
        ];

    }

    static function helpText(string $property = null, $header = '', $text = '', ?string $headerTag = null, ?array $headerTagAttributes = null, $width = 100, $propConfig = []): array
    {
        $headerTag = !empty($headerTag) ? $headerTag : (self::getHeaderTagConfig()['tag'] ?? 'h3');
        $headerTagAttributes = $headerTagAttributes ?? self::getHeaderTagConfig()['attributes'] ?? ['class' => 'form-section-headline'];
        $property = self::randomIdIfNull($property);
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


    static function select(string $property,array $options, array $propConfig = []): array
    {
        $items = self::convertOptionsToItemsArray($options);
        $config = [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => $items,
        ];
        return self::buildType($property, $config, $propConfig);
    }

    static function colorPicker($property, $propConfig = []): array
    {
        $config = ['type' => 'color', 'size' => 10];
        return self::buildType($property, $config, $propConfig);
    }

    static function linkToFile($property, array $allowedFileExtensions = [], $propConfig = []): array
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
        return self::buildType($property, $config, $propConfig);
    }

    static function linkToPage($property, $propConfig = []): array
    {
        $config = ['type' => 'link','allowedTypes' => ['page']];
        return self::buildType($property, $config, $propConfig);
    }

    static function inputWithValuePicker($property, array $options, $size = '10', $propConfig = []): array
    {
        $items = self::convertOptionsToItemsArray($options, 1, 0);
        $config = [
            'type' => 'input',
            'valuePicker' => ['items' => $items],
            'size' => $size,
        ];
        return self::buildType($property, $config, $propConfig);
    }

    static function checkBox(string $property, array $options = [], string|int $cols = 1, $propConfig = []): array
    {
        if(!$options) {
            $options = [1 => ''];
            $propConfig = array_replace_recursive($propConfig, ['colClass' => 'col col-sm-auto col-md-auto']);
        }
        $items = self::convertOptionsToItemsArray($options);
        $config = [
            'type' => 'check',
            'items' => $items,
            'cols' => $cols
        ];
        return self::buildType($property, $config, $propConfig);
    }

    static function radioButtons(string $property, array $options = [], array $propConfig = []): array
    {
        $items = self::convertOptionsToItemsArray($options);
        $config = [
            'type' => 'radio',
            'items' => $items,
        ];
        return self::buildType($property, $config, $propConfig);
    }

    static function convertOptionsToItemsArray($options, $keyForKey = 'value', $keyForValue = 'label' ): array
    {
        if(is_array($options[0] ?? null)){
            return $options;
        }
        $callback = fn(string $k, string $v): array => [$keyForKey => $k, $keyForValue => $v];
        return array_map($callback, array_keys($options), array_values($options));
    }





    static function randomIdIfNull($id)
    {
        return is_null($id) ? bin2hex(random_bytes(5)) : $id;
    }

    static function buildType($property, $config, $propModify, $helpText = ''): array
    {
        $config = $config ?? [];
        static::addFieldInformationConfiguration($config);
        static::addFieldWizardConfiguration($config);
        $propConfig = ['property' => $property, 'helpText' => $helpText, 'config' => $config];
        return array_replace_recursive($propConfig, $propModify);
    }

    static function addFieldInformationConfiguration(&$config): void {
        if(!is_array($config)) { return;}
        $config['fieldInformation'] = array_merge(
            $config['fieldInformation'] ?? [],
            [['renderType' => 'staticText']]);
    }
    static function addFieldWizardConfiguration(&$config): void {
        if(!is_array($config)) { return;}
        $config['fieldWizard'] = array_merge(
            $config['fieldWizard'] ?? [],
            [['renderType' => 'resetFieldButton'],]);
    }
}
