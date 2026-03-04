<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Hook;

use Buepro\Easyconf\Event\BeforePersistingPropertiesEvent;
use Buepro\Easyconf\Mapper\MapperInterface;
use Buepro\Easyconf\Mapper\MapperRegistry;
use Buepro\Easyconf\Mapper\ServiceManager;
use Buepro\Easyconf\Mapper\TypoScriptConstantMapper;
use Buepro\Easyconf\Service\DatabaseService;
use Buepro\Easyconf\TypoScript\ConstantSubstitutor;
use Buepro\Easyconf\Utility\TcaUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class DataHandlerHook implements SingletonInterface
{
    /**
     * If this data is set all mapped properties will be persisted in processDatamap_afterAllOperations.
     *
     * @var ?array ['tableUid' => $id, 'formFields' => $incomingFieldArray]
     */
    protected static ?array $configurationData = null;

    protected bool $processed = false;

    protected EventDispatcherInterface $eventDispatcher;
    protected ConstantSubstitutor $constantSubstitutor;
    public function __construct(private readonly \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool, \Psr\EventDispatcher\EventDispatcherInterface $eventDispatcher, \Buepro\Easyconf\TypoScript\ConstantSubstitutor $constantSubstitutor)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->constantSubstitutor = $constantSubstitutor;
    }

    public function processDatamap_preProcessFieldArray(
        array &$incomingFieldArray,
        string $table,
        string $id,
        DataHandler $dataHandler
    ): void {
        if (
            $table === 'tx_easyconf_configuration' &&
            $incomingFieldArray !== [] &&
            ($uid = (int)$id) > 0 &&
            MathUtility::canBeInterpretedAsInteger($id)
        ) {
            self::$configurationData = [
                'tableUid' => $uid,
                'formFields' =>$incomingFieldArray,
            ];
            $this->writePropertiesToBuffer($incomingFieldArray, $uid);
            $this->filterIncomingFieldArray($incomingFieldArray);
        }
    }

    public function processDatamap_afterAllOperations(DataHandler $dataHandler): void
    {
        if (
            self::$configurationData !== null &&
            is_array($configurationRecord = $this->connectionPool
                ->getConnectionForTable('tx_easyconf_configuration')
                ->select(['*'], 'tx_easyconf_configuration', ['uid' => self::$configurationData['tableUid']])
                ->fetchAssociative())
        ) {
            $this->substituteNewWithUid($dataHandler);
            $this->eventDispatcher->dispatch(new BeforePersistingPropertiesEvent(
                self::$configurationData['formFields'],
                $configurationRecord
            ));
            $this->constantSubstitutor->substitute();
            self::$configurationData = null;
            foreach (MapperRegistry::getInstances() as $mapper) {
                $mapper->persistBuffer();
                if (is_numeric($configurationRecord['pid'])) {
                    $mapper->processFuncAfterBufferPersisted((int)$configurationRecord['pid']);
                }
            }
            if ((bool)GeneralUtility::makeInstance(TypoScriptConstantMapper::class)->getProperty(
                'module.tx_easyconf.persistence.clearPageCache'
            )) {
                if (is_numeric($configurationRecord['pid'])) {
                    \Buepro\Easyconf\Utility\GeneralUtility::flushPagesCache((int)$configurationRecord['pid']);
                }
            }
        }
    }

    protected function writePropertiesToBuffer(array $data, int $id): void
    {
        if (
            ($columns = $GLOBALS['TCA']['tx_easyconf_configuration']['columns'] ?? null) !== null &&
            MathUtility::canBeInterpretedAsInteger($pageUid = GeneralUtility::makeInstance(DatabaseService::class)
                ->getField('tx_easyconf_configuration', 'pid', ['uid' => $id])) &&
            ($pageUid = (int)max(0, $pageUid)) > 0 &&
            /** @extensionScannerIgnoreLine */
            GeneralUtility::makeInstance(ServiceManager::class)->init($pageUid)
        ) {
            foreach ($columns as $columnName => $columnConfig) {
                    $path = TcaUtility::getMappingPath($columnName);
                    $class = TcaUtility::getMappingClass($columnName);
                if (
                    $path !== null &&
                    $class !== null && class_exists($class)
                ) {
                    $mapperInstance = GeneralUtility::makeInstance($class);
                    if($mapperInstance instanceof MapperInterface) {

                        // Buffer all properties visible in the form
                        if (isset($data[$columnName])) {
                            $mapperInstance->bufferProperty(
                                $path,
                                TcaUtility::mapFormToMapperValue($columnName, $data[$columnName])
                            );
                            continue;
                        }
                        // Buffer as well all TS constant properties where the value differs from the inherited value.
                        // Like this once set values are not overwritten when a field isn't visible.
                        if (
                            $mapperInstance instanceof TypoScriptConstantMapper &&
                            $mapperInstance->getProperty($path) !== $mapperInstance->getInheritedProperty($path)
                        ) {
                            $mapperInstance->bufferProperty(
                                $path,
                                TcaUtility::mapFormToMapperValue($columnName, $mapperInstance->getProperty($path))
                            );
                        }
                    }
                }
            }
        }
    }

    protected function filterIncomingFieldArray(array &$incomingFieldArray): void
    {
        $allowedFields = array_flip(GeneralUtility::trimExplode(
            ',',
            $GLOBALS['TCA']['tx_easyconf_configuration']['ctrl']['EXT']['easyconf']['dataHandlerAllowedFields'] ?? ''
        ));
        $incomingFieldArray = array_filter(
            $incomingFieldArray,
            static fn ($field) => isset($allowedFields[$field]),
            ARRAY_FILTER_USE_KEY
        );
    }

    protected function substituteNewWithUid(DataHandler $dataHandler): void
    {
        if (self::$configurationData === null) {
            return;
        }
        foreach (self::$configurationData['formFields'] as $formFieldName => $formFieldValue) {
            if (
                strpos($formFieldValue, 'NEW') === false
            ) {
                continue;
            }
            $mixedUids = GeneralUtility::trimExplode(',', $formFieldValue, true);
            $uids = [];
            foreach ($mixedUids as $mixedUid) {
                if (($uid = (int)($dataHandler->substNEWwithIDs[$mixedUid] ?? 0)) > 0) {
                    $uids[] = $uid;
                }
            }
            self::$configurationData['formFields'][$formFieldName] = implode(',', $uids);
        }
    }
}
