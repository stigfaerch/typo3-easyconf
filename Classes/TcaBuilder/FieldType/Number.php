<?php

namespace Buepro\Easyconf\TcaBuilder\FieldType;

class Number extends AbstractFieldType {
    protected ?int $rangeStart = null;
    protected ?int $rangeEnd = null;
    protected bool $slider = false;
    protected ?int $sliderStep = 1;
    protected ?int $sliderWidth = 200;

    public function range(int $start, int $end): self {
        $this->rangeStart = $start;
        $this->rangeEnd = $end;
        return $this;
    }

    public function slider(bool $enabled = true): self {
        $this->slider = $enabled;
        return $this;
    }

    public function sliderStep(int $step): self {
        $this->sliderStep = $step;
        return $this;
    }

    public function sliderWidth(int $width): self {
        $this->sliderWidth = $width;
        return $this;
    }

    public function generateConfig(): array {
        $config = parent::getConfig();
        if ($this->rangeStart !== null && $this->rangeEnd !== null) {
            $config['range'] = [
                'lower' => $this->rangeStart,
                'upper' => $this->rangeEnd,
            ];
        }
        if ($this->slider) {
            $config['slider']['step'] = $this->sliderStep ?? 1;
            $config['slider']['width'] = $this->sliderWidth ?? 100;
        }
        return $config;
    }
}
