<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Form;

use TYPO3\CMS\Backend\Form\FormDataProvider\EvaluateDisplayConditions;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Exception;

class DisplayConditionFunctions
{

    public static function doesPageExist(array $params, EvaluateDisplayConditions $pObj): bool
    {
        static $cache = [];

        $currentPageId = self::getPid($params);

        if (isset($cache[$currentPageId])) {
            return $cache[$currentPageId];
        }
        $pageId = \Buepro\Easyconf\Utility\GeneralUtility::getValue($currentPageId, $params);
        if (is_numeric($pageId) && $pageId > 0) {
            if ((bool) BackendUtility::getRecord('pages', (int) $pageId, 'uid')) {
                return $cache[$currentPageId] = true;
            }
        }
        return $cache[$currentPageId] = false;
    }

    public static function doesPageNotExist(array $params, EvaluateDisplayConditions $pObj): bool
    {
        return !self::doesPageExist($params, $pObj);
    }

    public static function getPid(array $params): int
    {
        if ($params['effectivePid'] ?? false) {
            return (int)$params['effectivePid'];
        } else {
            $row = $params['record'] ?? $params['row'];
            if (($row['uid'] ?? false) && ($row['pid'] ?? false)) {
                return (int)BackendUtility::getTSCpid('tx_news_domain_model_news', $row['uid'], $row['pid'])[0];
            }
        }
        throw new Exception('pid could not be found', 1707305656);
    }
}
