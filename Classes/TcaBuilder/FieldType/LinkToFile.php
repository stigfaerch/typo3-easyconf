<?php

namespace Buepro\Easyconf\TcaBuilder\FieldType;

class LinkToFile extends AbstractFieldType
{
    protected array $allowedFileExtensions = [];

    public function allowedFileExtensions(array $allowedFileExtensions): LinkToFile
    {
        $this->allowedFileExtensions = $allowedFileExtensions;
        return $this;
    }

    protected function generateConfig(): array
    {
        $config = ['type' => 'link', 'allowedTypes' => ['file']];
        $config = array_merge($config, ['fieldInformation' => [
            'linkImagePreview' => [
                'renderType' => 'linkImagePreview',
            ]
        ]
        ]);
        if ($this->allowedFileExtensions !== []) {
            $config['appearance']['allowedFileExtensions'] = $this->allowedFileExtensions;
            //            $config['appearance']['allowedExtensions'] = $allowedFileExtensions;
            //            $config['appearance']['enableBrowser'] = false;
            //            $config['appearance']['browserTitle'] = 'Browser Title';
        }
        return $config;

    }
}
