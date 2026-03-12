<?php

namespace Buepro\Easyconf\TcaBuilder\FieldType;

class InputWithValuePicker extends AbstractFieldType
{
    protected string|int $size = 10;
    protected array $options = [];

    public function setSize(int|string $size): InputWithValuePicker
    {
        $this->size = $size;
        return $this;
    }

    public function options(array $data): InputWithValuePicker
    {
        $this->options = $data;
        return $this;
    }

    protected function generateConfig(): array
    {
        $items = $this->convertOptionsToItemsArray($this->options, 1, 0);
        return [
            'type' => 'input',
            'valuePicker' => ['items' => $items],
            'size' => $this->size,
        ];
    }
}
