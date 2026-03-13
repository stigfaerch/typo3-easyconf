..  include:: /Includes.rst.txt

..  _usingTcaBuilder:

================
Using TcaBuilder
================

The TcaBuilder feature provides a fluent, object-oriented API for building TCA (Table Configuration Array)
configurations for the Easyconf extension. Instead of manually creating complex array structures, you can
use a chainable API with dedicated field type classes.

Overview
========

The TcaBuilder allows you to:

* Create configuration types with tabs and palettes
* Define form fields using intuitive FieldType classes
* Map fields to TypoScript constants, site configuration, or database records
* Generate configuration with a fluent, readable syntax

Basic Usage
===========

Here's a minimal example of using the TcaBuilder:

..  code-block:: php

    use Buepro\Easyconf\TcaBuilder\TcaBuilder;
    use Buepro\Easyconf\Service\Mapping;
    use Buepro\Easyconf\TcaBuilder\FieldType\Input;
    use Buepro\Easyconf\TcaBuilder\FieldType\ColorPicker;

    $builder = new TcaBuilder();
    $builder->init('LLL:EXT:my_extension/Resources/Private/Language/locallang_db.xlf');

    $builder->createType(0)
        ->init('EXT:my_extension/Resources/Public/Icons/settings.svg')
        ->addTab('general', 'General Settings')
        ->addPalette('colors', 1, 'Color Configuration')
        ->map(
            Mapping::forConstants('constants.colors', 'colors')->with(
                ColorPicker::create('primary')->label('Primary Color')->default('#0066cc'),
                ColorPicker::create('secondary')->label('Secondary Color')->default('#ff6600'),
                Input::create('siteName')->label('Site Name')->default('My Website')
            )
        )
        ->buildConfiguration();

    $builder->generateColumnData();
    $builder->generateConstantFile();

TcaBuilder Class
================

The main TcaBuilder class orchestrates the configuration building process.

Initialization
--------------

..  code-block:: php

    $builder = new TcaBuilder();
    $builder->init('LLL:EXT:my_extension/Resources/Private/Language/locallang.xlf');

The ``init()`` method takes a locallang file path that will be used for field labels.

Creating Types
--------------

..  code-block:: php

    $builder->createType(0)
        ->init(
            'EXT:my_extension/Resources/Public/Icons/appearance.svg',  // Icon
            'Type Title',                                               // Title (optional)
            'Type Subtitle',                                            // Subtitle (optional)
            'Type Description',                                         // Description (optional)
            'Editors',                                                  // Required backend user group (optional)
            'pids.settings.storage'                                     // Page UID from site setting (optional)
        );

Adding Tabs
-----------

Tabs organize fields into logical sections:

..  code-block:: php

    $builder->createType(0)
        ->init('EXT:my_extension/Resources/Public/Icons/settings.svg')
        ->addTab('general', 'General Settings')
        ->addTab('appearance', 'Appearance')
        ->addTab('advanced', 'Advanced Options');

Adding Palettes
---------------

Palettes group related fields within a tab:

..  code-block:: php

    ->addPalette(
        'colors',          // Palette ID (optional, auto-generated if null)
        4,                 // Line break period (fields per row)
        'Color Settings',  // Header text
        'h2'               // Header tag (optional)
    )

Type Class
==========

The Type class represents a configuration type and provides methods for organizing fields.

Methods
-------

``addTab(string $id = null, ?string $tabName = null): static``
    Add a new tab to the type

``addPalette(string $id = null, int $lineBreakPeriod = 1, string $header = '', ?string $headerTag = null): Palette``
    Add a new palette (field group) to the type

``map(Mapping ...$mappings): static``
    Add field mappings directly to the type

``buildConfiguration(): void``
    Build the final TCA configuration

``setClearCacheForSite(): static``
    Enable site cache clearing when this type is saved

Palette Class
=============

The Palette class groups related fields together.

..  code-block:: php

    ->addPalette('typography', 3, 'Typography Settings')->map(
        Mapping::forConstants('constants.typography', 'typography')->with(
            Select::create('font')->options($fonts)->label('Font Family'),
            Number::create('fontSize')->default(16)->label('Font Size'),
            CheckBox::create('bold')->label('Bold')->default(0)
        )
    )

Mapping
=======

The Mapping class connects fields to their storage locations.

Static Factory Methods
----------------------

``Mapping::forConstants(string $path, string $fieldPrefix): Mapping``
    Map to TypoScript constants

..  code-block:: php

    Mapping::forConstants('constants.appearance.colors', 'appearance_colors')

``Mapping::create(string $mapper, string $path, string $fieldPrefix): Mapping``
    Create a custom mapping with a specific mapper class

..  code-block:: php

    use Buepro\Easyconf\Mapper\RecordMapper;

    Mapping::create(RecordMapper::class, 'constants.settings', 'settings')

Adding Fields
-------------

Use the ``with()`` method to add FieldType instances:

..  code-block:: php

    Mapping::forConstants('constants.colors', 'colors')->with(
        ColorPicker::create('primary')->label('Primary Color'),
        ColorPicker::create('secondary')->label('Secondary Color')
    )

FieldType Classes
=================

All FieldType classes extend ``AbstractFieldType`` and provide a fluent API for building form fields.

Common Methods
--------------

All FieldType classes support these methods:

``create(?string $field = null): static``
    Static factory method to create a new instance

``label(string $label): AbstractFieldType``
    Set the field label

``default(mixed $default): AbstractFieldType``
    Set the default value

``helpText(string $helpText): AbstractFieldType``
    Set help text for the field

``properties(array $properties): AbstractFieldType``
    Merge additional properties into the field configuration

Input
-----

Basic text input field:

..  code-block:: php

    Input::create('siteName')
        ->label('Site Name')
        ->default('My Website')
        ->helpText('Enter your website name')

Number
------

Numeric input field with optional range and slider:

..  code-block:: php

    Number::create('fontSize')
        ->label('Font Size')
        ->default(16)
        ->range(10, 72)
        ->slider(true)
        ->sliderStep(2)
        ->sliderWidth(300)

Methods
~~~~~~~

``range(int $start, int $end): self``
    Set the allowed numeric range

``slider(bool $enabled = true): self``
    Enable slider UI

``sliderStep(int $step): self``
    Set slider step value

``sliderWidth(int $width): self``
    Set slider width in pixels

ColorPicker
-----------

Color picker field:

..  code-block:: php

    ColorPicker::create('primaryColor')
        ->label('Primary Color')
        ->default('#0066cc')
        ->helpText('Choose your primary brand color')

Select
------

Dropdown selection field:

..  code-block:: php

    Select::create('theme')
        ->label('Theme')
        ->default('light')
        ->options([
            'light' => 'Light Theme',
            'dark' => 'Dark Theme',
            'auto' => 'Auto (System)'
        ])

RadioButtons
------------

Radio button group:

..  code-block:: php

    RadioButtons::create('layout')
        ->label('Layout Type')
        ->default('fluid')
        ->options([
            'fixed' => 'Fixed Width',
            'fluid' => 'Fluid Width',
            'boxed' => 'Boxed'
        ])

CheckBox
--------

Checkbox field (single or multiple):

..  code-block:: php

    // Single checkbox
    CheckBox::create('enabled')
        ->label('Enable Feature')
        ->default(1)

    // Multiple checkboxes
    CheckBox::create('features')
        ->label('Features')
        ->options([
            'newsletter' => 'Newsletter',
            'comments' => 'Comments',
            'social' => 'Social Sharing'
        ])
        ->setCols(3)
        ->setInvertStateDisplay(true)

Methods
~~~~~~~

``options(array $data): CheckBox``
    Set checkbox options (for multiple checkboxes)

``setCols(int|string $cols): CheckBox``
    Set number of columns for multiple checkboxes

``setInvertStateDisplay(bool|array $invertStateDisplay = true): CheckBox``
    Invert the display state (show unchecked instead of checked)

InputWithValuePicker
--------------------

Input field with predefined value picker:

..  code-block:: php

    InputWithValuePicker::create('padding')
        ->label('Padding')
        ->default('10px')
        ->options([
            '0px' => 'None',
            '5px' => 'Small',
            '10px' => 'Medium',
            '20px' => 'Large',
            '40px' => 'Extra Large'
        ])
        ->properties(['config' => ['eval' => 'trim']])

Methods
~~~~~~~

``options(array $data): InputWithValuePicker``
    Set the value picker options

``setSize(int|string $size): InputWithValuePicker``
    Set input field size

LinkToFile
----------

File link selector:

..  code-block:: php

    LinkToFile::create('logo')
        ->label('Logo')
        ->default(158)  // File UID
        ->helpText('Select your logo file (SVG or PNG recommended)')
        ->allowedFileExtensions(['svg', 'png', 'jpg', 'gif'])

Methods
~~~~~~~

``allowedFileExtensions(array $allowedFileExtensions): LinkToFile``
    Restrict allowed file types

LinkToPage
----------

Page link selector:

..  code-block:: php

    LinkToPage::create('homepage')
        ->label('Homepage')
        ->default(1)
        ->helpText('Select your homepage')

HelpText
--------

Static help text or header:

..  code-block:: php

    HelpText::create()
        ->header('Important Information')
        ->text('Please configure these settings carefully.')
        ->headerTag('h3')
        ->width(100)

Methods
~~~~~~~

``header(string $header): HelpText``
    Set header text

``text(string $text): HelpText``
    Set body text

``headerTag(string $headerTag): HelpText``
    Set header HTML tag (h2, h3, etc.)

``width(int|string $width): HelpText``
    Set width percentage

Custom
------

Custom field with full control over configuration:

..  code-block:: php

    Custom::create('customButton')
        ->properties([
            'config' => [
                'type' => 'user',
                'renderType' => 'customButtonRenderer',
                'label' => 'Custom Action',
                'parameters' => ['action' => 'doSomething']
            ],
            'displayCond' => 'FIELD:enabled:=:1'
        ])

Layout Helper Types
-------------------

Linebreak
~~~~~~~~~

Insert a line break in the form:

..  code-block:: php

    Linebreak::create()

Blank
~~~~~

Insert an empty space/placeholder:

..  code-block:: php

    Blank::create()

Complete Example
================

Here's a comprehensive example showing various features:

..  code-block:: php

    use Buepro\Easyconf\TcaBuilder\TcaBuilder;
    use Buepro\Easyconf\Service\Mapping;
    use Buepro\Easyconf\TcaBuilder\FieldType\*;
    use Buepro\Easyconf\Utility\TcaBuilderUtility;

    $fonts = [
        'arial' => 'Arial',
        'georgia' => 'Georgia',
        'helvetica' => 'Helvetica'
    ];

    $builder = new TcaBuilder();
    $builder->init('LLL:EXT:my_extension/Resources/Private/Language/locallang_db.xlf');

    // Set global header tag configuration
    TcaBuilderUtility::setHeaderTagConfig('h2', ['styling' => 'font-weight:bold;']);

    $builder->createType(0)
        ->init('EXT:my_extension/Resources/Public/Icons/appearance.svg')

        // Colors Tab
        ->addTab('colors', 'Colors')
        ->addPalette('brandColors', 4, 'Brand Colors')->map(
            Mapping::forConstants('constants.appearance.colors', 'brand_colors')->with(
                HelpText::create()
                    ->header('Choose Your Brand Colors')
                    ->text('Select the main colors for your website.'),
                Linebreak::create(),
                ColorPicker::create('primary')->default('#0066cc')->label('Primary Color'),
                ColorPicker::create('secondary')->default('#ff6600')->label('Secondary Color'),
                ColorPicker::create('accent')->default('#00cc66')->label('Accent Color'),
                ColorPicker::create('background')->default('#ffffff')->label('Background Color')
            )
        )

        // Typography Tab
        ->addTab('typography', 'Typography')
        ->addPalette('headingFont', 3, 'Heading Font')->map(
            Mapping::forConstants('constants.typography.heading', 'typography_heading')->with(
                Select::create('font')->options($fonts)->default('arial')->label('Font Family'),
                Linebreak::create(),
                InputWithValuePicker::create('size')
                    ->options([
                        '24px' => 'Small (24px)',
                        '30px' => 'Medium (30px)',
                        '36px' => 'Large (36px)',
                        '42px' => 'Extra Large (42px)'
                    ])
                    ->default('30px')
                    ->label('Font Size'),
                Linebreak::create(),
                CheckBox::create('bold')->label('Bold')->default(1),
                CheckBox::create('italic')->label('Italic')->default(0),
                CheckBox::create('uppercase')->label('Uppercase')->default(0)
            )
        )

        // Layout Tab
        ->addTab('layout', 'Layout')
        ->addPalette('pageLayout', 1, 'Page Layout')->map(
            Mapping::forConstants('constants.layout', 'layout')->with(
                RadioButtons::create('width')
                    ->options([
                        '960px' => 'Narrow (960px)',
                        '1200px' => 'Standard (1200px)',
                        '1400px' => 'Wide (1400px)',
                        '100%' => 'Full Width'
                    ])
                    ->default('1200px')
                    ->label('Content Width'),
                Number::create('spacing')
                    ->label('Element Spacing')
                    ->default(20)
                    ->range(0, 100)
                    ->slider(true)
                    ->sliderStep(5)
            )
        )

        ->buildConfiguration();

    $builder->generateColumnData();
    $builder->generateConstantFile();

Advanced Features
=================

Setting Header Tag Configuration
---------------------------------

Configure global header tag styling for palettes:

..  code-block:: php

    TcaBuilderUtility::setHeaderTagConfig('h2', [
        'styling' => 'font-weight:bold; color: #333;',
        'class' => 'palette-header'
    ]);

Using Default References
-------------------------

Reference values from other constants:

..  code-block:: php

    ColorPicker::create('menuColor')
        ->label('Menu Color')
        ->properties(['defaultReference' => 'constants.appearance.colors.primary'])

This will use the value from another constant as the default.

Conditional Display
-------------------

Show/hide fields based on conditions:

..  code-block:: php

    Input::create('customValue')
        ->label('Custom Value')
        ->properties([
            'displayCond' => 'FIELD:enableCustom:=:1'
        ])

Custom Mappers
--------------

Use different mapper types for different storage targets:

..  code-block:: php

    use Buepro\Easyconf\Mapper\RecordMapper;
    use Buepro\Easyconf\Mapper\EasyconfMapper;

    // Map to database record
    Mapping::create(RecordMapper::class, 'settings', 'my_settings')->with(
        CheckBox::create('enabled')
            ->default(1)
            ->label('Enable Feature')
            ->properties([
                'RecordMapper' => [
                    'table' => 'pages',
                    'field' => 'hidden',
                    'pageId' => 'site-configuration:rootPageId'
                ]
            ])
    )

Tips and Best Practices
========================

1. **Use Meaningful Field Names**: Choose descriptive field names that clearly indicate their purpose.

2. **Organize with Tabs and Palettes**: Group related fields logically for better user experience.

3. **Provide Help Text**: Always add help text to guide users in filling out fields correctly.

4. **Set Sensible Defaults**: Provide default values that work well for most cases.

5. **Use Line Breaks**: Insert line breaks to improve form readability, especially in wide palettes.

6. **Validate Input**: Use the ``properties()`` method to add validators and constraints.

7. **Consider Mobile**: Be mindful of field complexity and layout on smaller screens.

8. **Test Configuration**: Always test your TCA configuration in the backend to ensure it works as expected.