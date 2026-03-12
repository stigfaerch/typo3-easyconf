<?php

namespace Buepro\Easyconf\TcaBuilder\FieldType;

class RadioButtons extends AbstractFieldType
{
    public function options(array $data): RadioButtons
    {
        $items = $this->convertOptionsToItemsArray($data);
        $config = [
            'type' => 'radio',
//            'renderType' => 'selectSingle',
            'items' => $items,
        ];
        $this->setConfig($config);
        return $this;
    }
}
