<?php

namespace Buepro\Easyconf\Service;

use Buepro\Easyconf\Mapper\TypoScriptConstantMapper;

class Mapping
{
    protected string $mapper;
    protected string $path;
    protected string $fieldPrefix = '';
    protected array $properties = [];


    protected string $configurationString = '';

    /**
     * @param string $path
     * @param string $fieldPrefix
     * @param string $mapper
     */
    public function __construct(string $path, string $fieldPrefix, string $mapper = TypoScriptConstantMapper::class)
    {
        $this->mapper = $mapper;
        $this->path = $path;
        $this->fieldPrefix = $fieldPrefix;
    }

    public static function create(string $mapper, string $path, string $fieldPrefix = ''): static
    {
        return new static($path, $fieldPrefix, $mapper);
    }

    public static function forConstants(string $path, string $fieldPrefix = ''): static
    {
        return new static($path, $fieldPrefix, TypoScriptConstantMapper::class);
    }

    public function getMapper(): string
    {
        return $this->mapper;
    }


    public function getPath(): string
    {
        return $this->path;
    }

    public function getFieldPrefix(): string
    {
        return $this->fieldPrefix;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function withProperties(array $properties): static
    {
        $this->properties = $properties;
        return $this;
    }

    public function getConfigurationString(): string
    {
        return $this->configurationString;
    }

    public function setConfigurationString(string $configurationString): void
    {
        $this->configurationString = $configurationString;
    }
}
