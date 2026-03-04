<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Utility;

class PropertyHelper
{
    private static ?array $headerTagConfig = [];

    public static function setHeaderTagConfig(string $tag, ?array $additionalAttributes = null): void
    {
        self::$headerTagConfig = self::getHeaderTagConfigFromArray([$tag, $additionalAttributes]);
    }

    public static function getHeaderTagConfig(): ?array
    {
        return self::$headerTagConfig;
    }

    public static function getHeaderTagConfigFromArray(array $config): array
    {
        $tag = $config[0] ?? $config['tag'] ?? null;
        $additionalAttributes = $config[1] ?? $config['additionalAttributes'] ?? null;
        return ['tag' => $tag, 'additionalAttributes' => $additionalAttributes];
    }

    public static function addClearCacheForSite(array $config): array
    {
        $config['clearCacheForSite'] = true;
        return $config;
    }

    public static function blank(): array
    {
        return [
            'property' => 'blank_' . self::randomIdIfNull(null),
            'config' => [
                'type' => 'blank',
            ]
        ];

    }

    public static function helpText(string $property = null, string $header = '', string $text = '', ?string $headerTag = null, ?array $headerTagAttributes = null, string|int $width = 100, array $propConfig = []): array
    {
        $headerTag = ($headerTag !== null && $headerTag !== '') ? $headerTag : (self::getHeaderTagConfig()['tag'] ?? 'h3');
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

    public static function select(string $property, array $options, array $propConfig = []): array
    {
        $items = self::convertOptionsToItemsArray($options);
        $config = [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => $items,
        ];
        return self::buildType($property, $config, $propConfig);
    }

    public static function colorPicker(string $property, array $propConfig = []): array
    {
        $config = ['type' => 'color', 'size' => 10];
        return self::buildType($property, $config, $propConfig);
    }

    public static function linkToFile(string $property, array $allowedFileExtensions = [], array $propConfig = []): array
    {
        $config = ['type' => 'link', 'allowedTypes' => ['file']];
        $config = array_merge($config, ['fieldInformation' => [
            'linkImagePreview' => [
                'renderType' => 'linkImagePreview',
            ]
        ]
        ]);
        if ($allowedFileExtensions !== []) {
            $config['appearance']['allowedFileExtensions'] = $allowedFileExtensions;
            //            $config['appearance']['allowedExtensions'] = $allowedFileExtensions;
            //            $config['appearance']['enableBrowser'] = false;
            //            $config['appearance']['browserTitle'] = 'Browser Title';
        }
        return self::buildType($property, $config, $propConfig);
    }

    public static function linkToPage(string $property, array $propConfig = []): array
    {
        $config = ['type' => 'link', 'allowedTypes' => ['page']];
        return self::buildType($property, $config, $propConfig);
    }

    public static function inputWithValuePicker(string $property, array $options, string|int $size = '10', array $propConfig = []): array
    {
        $items = self::convertOptionsToItemsArray($options, 1, 0);
        $config = [
            'type' => 'input',
            'valuePicker' => ['items' => $items],
            'size' => $size,
        ];
        return self::buildType($property, $config, $propConfig);
    }

    public static function checkBox(string $property, array $options = [], string|int $cols = 1, array $propConfig = []): array
    {
        if ($options !== []) {
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

    public static function radioButtons(string $property, array $options = [], array $propConfig = []): array
    {
        $items = self::convertOptionsToItemsArray($options);
        $config = [
            'type' => 'radio',
            'items' => $items,
        ];
        return self::buildType($property, $config, $propConfig);
    }

    public static function convertOptionsToItemsArray(array $options, string|int $keyForKey = 'value', string|int $keyForValue = 'label'): array
    {
        if (is_array($options[0] ?? null)) {
            return $options;
        }
        $callback = fn (string $k, string $v): array => [$keyForKey => $k, $keyForValue => $v];
        return array_map($callback, array_keys($options), array_values($options));
    }

    public static function randomIdIfNull(?string $id): string
    {
        return is_null($id) ? bin2hex(random_bytes(5)) : $id;
    }

    public static function buildType(string $property, array $config, array $propModify = [], string $helpText = ''): array
    {
        static::addFieldInformationConfiguration($config);
        static::addFieldWizardConfiguration($config);
        $propConfig = ['property' => $property, 'helpText' => $helpText, 'config' => $config];
        return array_replace_recursive($propConfig, $propModify);
    }

    public static function addFieldInformationConfiguration(array &$config): void
    {
        $config['fieldInformation'] = array_merge(
            $config['fieldInformation'] ?? [],
            [['renderType' => 'staticText']]
        );
    }
    public static function addFieldWizardConfiguration(array &$config): void
    {
        $config['fieldWizard'] = array_merge(
            $config['fieldWizard'] ?? [],
            [['renderType' => 'resetFieldButton']]
        );
    }
}
