<?php

namespace Buepro\Easyconf\TcaBuilder\FieldType;

class AbstractFieldType implements FieldTypeInterface
{
    protected mixed $default = null;

    protected ?string $label = null;

    protected ?string $helpText = null;

    protected array $properties = [];

    public function __invoke($helpText = ''): array
    {
        return $this->build($helpText);
    }

    protected array $config = [];

    public function __construct(readonly ?string $field = null)
    {
        return $this;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function default(mixed $default = null): AbstractFieldType
    {
        $this->default = $default;
        return $this;
    }

    public function label(string $label): AbstractFieldType
    {
        $this->label = $label;
        return $this;
    }

    public function helpText(string $helpText): AbstractFieldType
    {
        $this->helpText = $helpText;
        return $this;
    }

    public function properties(array $properties): AbstractFieldType
    {
        $this->properties = array_replace_recursive($this->properties, $properties);
        return $this;
    }

    protected function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public static function create(?string $field = null): static
    {
        return new static($field);
    }

    public static function randomIdIfNull(?string $id): string
    {
        return is_null($id) ? bin2hex(random_bytes(5)) : $id;
    }

//    public static function buildPropertyArray(string $field, array $config, array $propertiesModify = [], string $helpText = ''): array
//    {
//        static::addFieldInformationConfiguration($config);
//        static::addFieldWizardConfiguration($config);
//        $properties = ['property' => $field, 'helpText' => $helpText, 'config' => $config];
//        return array_replace_recursive($properties, $propertiesModify);
//    }

    /**
     * @param string $helpText
     * @return array
     */
    protected function build(string $helpText = ''): array
    {
        $properties = $this->getProperties();
        if (method_exists($this, 'generateConfig')) {
            $this->setConfig($this->generateConfig());
        }
        if (method_exists($this, 'addProperties')) {
            $properties = array_replace_recursive($this->properties, $this->addProperties());
        }
        if ($this->default !== null) {
            $properties['default'] = $this->default;
        }
        if ($this->label !== null) {
            $properties['label'] = $this->label;
        }
        if ($this->helpText !== null) {
            $properties['helpText'] = $this->helpText;
        }
        $config = $this->getConfig();
        if (get_class($this) !== HelpText::class) {
            static::addFieldInformationConfiguration($config);
            static::addFieldWizardConfiguration($config);
        }
        $propConfig = ['property' => $this->getField(), 'helpText' => $helpText, 'config' => $config];
        return array_replace_recursive($propConfig, $properties);
    }


    public static function addFieldInformationConfiguration(array &$config): void
    {
        $config['fieldInformation'] = array_merge(
            $config['fieldInformation'] ?? [],
            [['renderType' => 'staticText']]
        );
    }
    public static function addFieldWizardConfiguration(array &$config): void
    {
        $config['fieldWizard'] = array_merge(
            $config['fieldWizard'] ?? [],
            [['renderType' => 'resetFieldButton']]
        );
    }

    /**
     * Converts an associative array of options into an array of items with specified keys for the item structure.
     *
     * // Input
     * ['foo' => 'Foo Label', 'bar' => 'Bar Label']
     *
     * // Output (with defaults)
     * [
     * ['value' => 'foo', 'label' => 'Foo Label'],
     * ['value' => 'bar', 'label' => 'Bar Label']
     * ]
     *
     * @param array $options An associative array where keys and values represent the data to be converted.
     * @param string|int $keyForKey The key to be used for the item key in the resulting array. Defaults to 'value'.
     * @param string|int $keyForValue The key to be used for the item value in the resulting array. Defaults to 'label'.
     * @param null|bool|array $invertStateDisplay Whether to include an 'invertStateDisplay' key in each item. Defaults to null.
     *
     *
     * @return array An array of items where each item is an associative array with the specified keys.
     */
    public function convertOptionsToItemsArray(array $options, string|int $keyForKey = 'value', string|int $keyForValue = 'label', null|bool|array $invertStateDisplay = null): array
    {
        if(is_bool($invertStateDisplay)){
            $invertStateDisplay = [$invertStateDisplay];
        }
        if (is_array($options[0] ?? null)) {
            if ($invertStateDisplay !== null) {
                foreach ($options as $key => $value) {
                    $options[$key]['invertStateDisplay'] = $invertStateDisplay[$key] ?? 0;
                }
            }
            return $options;
        }
        $i = 0;
        if (is_array($invertStateDisplay)) {
            $callback = function (string $k, string $v) use ($keyForKey, $keyForValue, $invertStateDisplay, &$i): array {
                return [$keyForKey => $k, $keyForValue => $v, 'invertStateDisplay' => $invertStateDisplay[$i++] ?? 0];
            };
        } else {
            $callback = fn (string $k, string $v): array => [$keyForKey => $k, $keyForValue => $v];
        }
        return array_map($callback, array_keys($options), array_values($options));
    }

}
