<?php

namespace Buepro\Easyconf\Service;

use Buepro\Easyconf\Mapper\EasyconfMapper;
use Buepro\Easyconf\Utility\PropertyHelper;
use Buepro\Easyconf\Utility\TcaUtility;

class Palette
{
    protected Type $parentObject;
    protected ?string $id;
    protected int $lineBreakPeriod;
    protected string $header;
    protected ?string $headerTag;
    protected array $properties;

    public function __construct(Type $parentObject, $id, $lineBreakPeriod, $header, $headerTag)
    {
        $this->parentObject = $parentObject;
        $this->id = $id;
        $this->lineBreakPeriod = $lineBreakPeriod;
        $this->header = $header;
        $this->headerTag = $headerTag;
    }

    public function map(Mapping ...$mappings): Type
    {
        $propertiesBuilt = array_map(fn(Mapping $property) => $this->parentObject->build($property), $mappings);

        if(is_null($this->id)) { $this->id = bin2hex(random_bytes(5));}
        if($this->header) {
            $helpProp = $this->parentObject->build(
              Mapping::create(EasyconfMapper::class, '', $this->id . 'header')->withProperties([
                  PropertyHelper::helpText($this->id . 'header', $this->header, headerTag: $this->headerTag, propConfig: ['colClass' => 'my-0']),
              ])
            );
            $propertiesBuilt = array_merge ([$helpProp, '--linebreak--'], $propertiesBuilt);
        }
        $paletteConfig = TcaUtility::getPalette(implode(',', $propertiesBuilt), '', $this->lineBreakPeriod);
        if(PropertyHelper::getHeaderTagConfig() || $this->headerTag) {
            $paletteHeaderTagConfig = (PropertyHelper::getHeaderTagConfig() ?? PropertyHelper::getHeaderTagConfigFromArray($this->headerTag));
            $paletteConfig = array_merge($paletteConfig, ['headerTag' => $paletteHeaderTagConfig]);
        }
        $GLOBALS['TCA']['tx_easyconf_configuration']['palettes'][$this->id] = $paletteConfig;
        $this->parentObject->addPaletteToProperties($this->id);
        return $this->parentObject;
    }
}
