<?php

namespace Buepro\Easyconf\TcaBuilder\FieldType;

use Buepro\Easyconf\Utility\TcaBuilderUtility;

class HelpText extends AbstractFieldType {
    protected string $header = '';
    protected string $text = '';
    protected string $headerTag = '';
    protected ?array $headerTagAttributes = null;
    protected string|int $width = 100;

    public function header(string $header): HelpText
    {
        $this->header = $header;
        return $this;
    }

    public function text(string $text): HelpText
    {
        $this->text = $text;
        return $this;
    }

    public function headerTag(string $headerTag): HelpText
    {
        $this->headerTag = $headerTag;
        return $this;
    }

    public function headerTagAttributes(?array $headerTagAttributes): HelpText
    {
        $this->headerTagAttributes = $headerTagAttributes;
        return $this;
    }

    public function width(int|string $width): HelpText
    {
        $this->width = $width;
        return $this;
    }

    public function addProperties(): array
    {
        $this->headerTag = ($this->headerTag !== null && $this->headerTag !== '') ? $this->headerTag : (TcaBuilderUtility::getHeaderTagConfig()['tag'] ?? 'h3');
        $this->headerTagAttributes = $this->headerTagAttributes ?? TcaBuilderUtility::getHeaderTagConfig()['attributes'] ?? ['class' => 'form-section-headline'];
        $field = self::randomIdIfNull($this->field);
        return [
            'property' => 'staticText_' . $field,
            'helpText' => $this->text,
            'helpHeader' => $this->header,
            'headerTag' => $this->headerTag,
            'headerTagAttributes' => $this->headerTagAttributes,
            'config' => [
                'type' => 'staticText',
            ],
        ];
    }



}
