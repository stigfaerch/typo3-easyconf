<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Mapper;

use Buepro\Easyconf\Mapper\Service\EasyconfService;
use Buepro\Easyconf\Mapper\Service\RecordService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;

class RecordMapper extends AbstractMapper implements SingletonInterface
{
    protected RecordService $recordService;

    public function __construct(RecordService $recordService)
    {
        parent::__construct();
        $this->recordService = $recordService;
    }

    public function getProperty(string $path): string
    {
        return $this->buffer[$path] ?? $this->recordService->getFieldByPath($path);
    }

    public function persistBuffer(): MapperInterface
    {
        if (count($this->buffer) === 0) {
            return $this;
        }
        $fields = $this->recordService->getFields();
        foreach ($this->buffer as $path => $value) {
            $fields = ArrayUtility::setValueByPath($fields, $path, $value, '.');
        }
        $this->recordService->setFields($fields)->persistFields();
        return $this;
    }
}
