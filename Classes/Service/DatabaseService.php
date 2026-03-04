<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Service;

use TYPO3\CMS\Core\Exception;

class DatabaseService
{
    public function __construct(private readonly \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool) {}

    public function getField(string $table, string $field, array $constraint): false|string|int|float
    {
        $result = $this->connectionPool
            ->getConnectionForTable($table)
            ->select(
                [$field],
                $table,
                $constraint
            )->fetchOne();
        if (is_string($result) || is_int($result) || is_float($result)) {
            return (string)$result;
        }
        throw new Exception('Field "' . $field . '" does not exist in database table "' . $table . '"', 1772525931);
    }

    public function getRecord(string $table, array $constraint): ?array
    {
        $result = $this->connectionPool
            ->getConnectionForTable($table)
            ->select(
                ['*'],
                $table,
                $constraint
            )->fetchAssociative();
        return is_array($result) && $result !== [] ? $result : null;
    }

    public function addRecord(string $table, array $fields, array $types = []): array
    {
        $connection = $this->connectionPool->getConnectionForTable($table);
        $now = time();
        foreach (['tstamp', 'crdate'] as $fieldName) {
            if (!isset($fields[$fieldName])) {
                $fields[$fieldName] = $now;
            }
        }
        $connection->insert(
            $table,
            $fields,
            $types
        );

        $record = $this->getRecord($table, ['uid' => (int)$connection->lastInsertId()]);
        if(is_array($record) && $record !== []) {
            return $record;
        } else {
            throw new Exception('Failed to retrieve newly created record', 1772525795);
        }
    }
}
