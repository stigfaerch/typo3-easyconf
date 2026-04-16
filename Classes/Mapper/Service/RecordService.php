<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Mapper\Service;

use Buepro\Easyconf\Mapper\RecordMapper;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RecordService implements SingletonInterface, MapperServiceInterface
{
    // Konfiguration skal indeholde information om hvor den skal gemme (uid) og hvorfra den skal kunne hente igen (pid)
    protected array $configuration = [];
    protected array $fields = [];
    protected int $pageUid = 0;
    public function __construct(private readonly \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool) {}

    public function init(int $pageUid): self
    {
        $this->pageUid = $pageUid;
        // Find all columns where mapper is RecordMapper and get the value from the database
        foreach ($GLOBALS['TCA']['tx_easyconf_configuration']['columns'] as $columnKey => $columnData) {
            if (($columnData['tx_easyconf']['mapper'] ?? false) === RecordMapper::class) {
                if($pid = \Buepro\Easyconf\Utility\GeneralUtility::getValue($pageUid, $columnData['RecordMapper']['pageId']) ?? false) {
                    $columnData['RecordMapper']['pageId'] = (int)$pid;
                    $record = BackendUtility::getRecord($columnData['RecordMapper']['table'], (int)$pid, $columnData['RecordMapper']['field']);
                    $value = $record[$columnData['RecordMapper']['field']] ?? '';
                    $this->fields = ArrayUtility::setValueByPath($this->fields, $columnData['tx_easyconf']['path'], (string)$value, '.');
                    $this->configuration[$columnData['tx_easyconf']['path']] = $columnData;
                }
            }
        }
        return $this;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    public function getFieldByPath(string $path): string
    {
        $result = '';
        if (ArrayUtility::isValidPath($this->getFields(), $path, '.')) {
            $result = ArrayUtility::getValueByPath($this->getFields(), $path, '.');
        }
        return is_string($result) ? $result : '';
    }

    public function persistFields(): self
    {
        foreach (ArrayUtility::flatten($this->fields) as $key => $value) {
            $config = $this->configuration[$key];
            $this->connectionPool
                ->getConnectionForTable($config['RecordMapper']['table'])
                ->update(
                    $config['RecordMapper']['table'],
                    [$config['RecordMapper']['field'] => $value],
                    ['uid' => (int)$config['RecordMapper']['pageId']],
                );
        }
        /** @extensionScannerIgnoreLine */
        $this->init($this->pageUid);
        return $this;
    }
}
