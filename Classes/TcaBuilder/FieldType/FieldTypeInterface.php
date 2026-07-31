<?php

namespace Buepro\Easyconf\TcaBuilder\FieldType;

interface FieldTypeInterface
{
    public function __construct(?string $property = null);

//    public function setConfig(array $config): void;

    function getConfig(): array;

    function getField(): ?string;

    function getProperties(): array;
}
