<?php

namespace Buepro\Easyconf\TcaBuilder\FieldType;

class CheckBox extends AbstractFieldType
{
    protected string|int $cols = 1;
    /** @var bool|array<bool>  */
    protected array $options = [];
    protected null|array|bool $invertStateDisplay = null;

    public function setCols(int|string $cols): CheckBox
    {
        $this->cols = $cols;
        return $this;
    }

    public function setInvertStateDisplay(bool|array $invertStateDisplay = true): CheckBox
    {
        $this->invertStateDisplay = $invertStateDisplay;
        return $this;
    }

    public function options(array $data): CheckBox
    {
        $this->options = $data;
        return $this;
    }

    protected function generateConfig(): array
    {
        if ($this->options === []) {
            $this->options = [1 => ''];
        }
        $items = $this->convertOptionsToItemsArray($this->options, 'value', 'label', $this->invertStateDisplay);
        return [
            'type' => 'check',
            'items' => $items,
            'cols' => $this->cols
        ];
    }

    protected function addProperties(): array
    {
        if ($this->options !== []) {
            return ['colClass' => 'col col-sm-auto col-md-auto'];
        }
        return [];
    }
}
