<?php

namespace Buepro\Easyconf\TcaBuilder\FieldType;

class LinkToPage extends AbstractFieldType
{
    protected array $allowedFileExtensions = [];

    public function allowedFileExtensions(array $allowedFileExtensions): LinkToPage
    {
        $this->allowedFileExtensions = $allowedFileExtensions;
        return $this;
    }

    protected function generateConfig(): array
    {
        return ['type' => 'link', 'allowedTypes' => ['page']];
    }
}
