<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Service;

use Buepro\Easyconf\Utility\PropertyHelper;
use Buepro\Easyconf\Utility\TcaUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Type
{
    protected int $type;

    protected array $properties = [];

    protected ?bool $clearCacheForSite;

    protected string $l10nFile;

    protected array $configuration = [];

    protected TcaBuilderService $tcaBuilderService;

    public function __construct(TcaBuilderService $tcaBuilderService, int $type)
    {
        $this->tcaBuilderService = $tcaBuilderService;
        $this->type = $type;
        $this->l10nFile = $tcaBuilderService->getL10nFile();
    }

    public function init(string $cardIcon = '', string $title = null, string $subtitle = null, string $description = null, string $requiredBackendUserGroup = null, string $pageUidFromSiteSetting = null): static
    {
        $this->configuration =
            [
                'title' => $title ?? ($this->l10nFile . ':type_' . $this->type . '_title'),
                'subtitle' => $subtitle ?? ($this->l10nFile . ':type_' . $this->type . '_subtitle'),
                'description' => $description ?? ($this->l10nFile . ':type_' . $this->type . '_description'),
                'cardIcon' => $cardIcon,
                'requiredBackendUserGroup' => $requiredBackendUserGroup ?? '',
                'pageUidFromSiteSetting' => $pageUidFromSiteSetting ?? '',
            ];
        return $this;
    }

    public function addPalette(string $id = null, int $lineBreakPeriod = 1, string $header = '', ?string $headerTag = null): Palette
    {
        return new Palette($this, $id, $lineBreakPeriod, $header, $headerTag);
    }

    public function addTab(string $id = null, ?string $tabName = null): static
    {
        if (is_null($id)) {
            $id = bin2hex(random_bytes(5));
        }
        $this->properties[] = '--div--;' . ($tabName ?? $this->l10nFile . ':' . $id);
        return $this;
    }

    public function buildConfiguration(): void
    {
        $GLOBALS['TCA']['tx_easyconf_configuration']['types'][$this->type] = array_merge($this->configuration, ['showitem' => implode(', ', $this->properties)]);
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    public function getConfiguration(): array
    {
        return $GLOBALS['TCA']['tx_easyconf_configuration']['types'][$this->type] ?? [];
    }

    public function isClearCacheForSite(): ?bool
    {
        return $this->clearCacheForSite;
    }

    public function setClearCacheForSite(): static
    {
        $this->clearCacheForSite = true;
        return $this;
    }

    public function unsetClearCacheForSite(): static
    {
        $this->clearCacheForSite = null;
        return $this;
    }

    public function map(Mapping ...$properties): static
    {
        foreach ($properties as $propertiesObject) {
            $this->properties[] = $this->build($propertiesObject);
        }
        return $this;
    }

    public function addPaletteToProperties(string $id): void
    {
        $this->properties[] = '--palette--;;' . $id;
    }

    public function build(Mapping $mapping): string
    {
        $fieldPrefix = GeneralUtility::camelCaseToLowerCaseUnderscored($mapping->getFieldPrefix());
        $newPropertiesForPropertyMap = [];
        $newProperties = [];
        $modify = [];
        foreach ($mapping->getProperties() as $key => $value) {
            if (is_int($key)) {
                if (is_array($value)) {
                    $newPropertiesForPropertyMap[] = $newProperties[] = $value['property'] ?? $key;
                    $modify[] = $value;
                } elseif ($value === '--linebreak--') {
                    $newProperties[] = $value;
                } else {
                    $newPropertiesForPropertyMap[] = $newProperties[] = $value;
                    $modify[] = null;
                }
            } else {
                $newProperties[] = $newPropertiesForPropertyMap[] = $key;
                $value['config'] = $value['config'] ?? [];
                $value['property'] = $key;
                if ($value['displayCond'] ?? false) {
                    $value['displayCond'] = $value['displayCond'];
                }
                PropertyHelper::addFieldInformationConfiguration($value['config']);
                PropertyHelper::addFieldWizardConfiguration($value['config']);
                $modify[] = $value;
            }
        }
        $propertyList = implode(',', $newProperties);
        $propertyListForPropertyMap = implode(',', $newPropertiesForPropertyMap);
        $this->tcaBuilderService->addToPropertyMap(TcaUtility::getPropertyMap($mapping->getMapper(), $mapping->getPath(), $propertyListForPropertyMap, $fieldPrefix, '', $modify));
        $mapping->setConfigurationString(TcaUtility::getFieldList($propertyList, $fieldPrefix));
        return $mapping->getConfigurationString();
    }
}
