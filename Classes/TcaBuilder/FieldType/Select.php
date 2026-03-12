<?php

namespace Buepro\Easyconf\TcaBuilder\FieldType;

class Select extends AbstractFieldType
{
    public function options(array $data): Select
    {
        $items = $this->convertOptionsToItemsArray($data);
        $config = [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => $items,
        ];
        $this->setConfig($config);
        return $this;
    }
}
