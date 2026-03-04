<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Service;

use Buepro\Easyconf\Mapper\EasyconfMapper;
use Buepro\Easyconf\Utility\PropertyHelper;
use Buepro\Easyconf\Utility\TcaUtility;

class Palette
{
    protected Type $parentObject;
    protected string $propertyId;
    protected int $lineBreakPeriod;
    protected string $header;
    protected ?string $headerTag;
    protected array $properties;

    public function __construct(Type $parentObject, ?string $id, int $lineBreakPeriod, string $header, ?string $headerTag)
    {

        $this->parentObject = $parentObject;
        $this->propertyId = is_null($id) ? bin2hex(random_bytes(5)) : $id;
        $this->lineBreakPeriod = $lineBreakPeriod;
        $this->header = $header;
        $this->headerTag = $headerTag;
    }

    public function map(Mapping ...$mappings): Type
    {
        $propertiesBuilt = array_map(fn (Mapping $property) => $this->parentObject->build($property), $mappings);

        if ($this->header !== '') {
            $helpProp = $this->parentObject->build(
                Mapping::create(EasyconfMapper::class, '', $this->propertyId . 'header')->withProperties([
                  PropertyHelper::helpText($this->propertyId . 'header', $this->header, headerTag: $this->headerTag, propConfig: ['colClass' => 'my-0']),
              ])
            );
            $propertiesBuilt = array_merge([$helpProp, '--linebreak--'], $propertiesBuilt);
        }
        $paletteConfig = TcaUtility::getPalette(implode(',', $propertiesBuilt), '', $this->lineBreakPeriod);
        if (PropertyHelper::getHeaderTagConfig() !== null || $this->headerTag !== null) {
            $paletteHeaderTagConfig = (PropertyHelper::getHeaderTagConfig() ?? PropertyHelper::getHeaderTagConfigFromArray([$this->headerTag]));
            $paletteConfig = array_merge($paletteConfig, ['headerTag' => $paletteHeaderTagConfig]);
        }
        $GLOBALS['TCA']['tx_easyconf_configuration']['palettes'][$this->propertyId] = $paletteConfig;
        $this->parentObject->addPaletteToProperties($this->propertyId);
        return $this->parentObject;
    }
}
