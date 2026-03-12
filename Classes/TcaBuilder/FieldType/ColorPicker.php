<?php

namespace Buepro\Easyconf\TcaBuilder\FieldType;

class ColorPicker extends AbstractFieldType {

    public function generateConfig(): array
    {
        return ['type' => 'color', 'size' => 10];
    }

}
