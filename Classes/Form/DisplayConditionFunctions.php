<?php

namespace Buepro\Easyconf\Form;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Exception;

class DisplayConditionFunctions
{

    public static function doesPageExist($params, $pObj): bool
    {
        static $cache = [];

        $currentPageId = self::getPid($params);

        if(isset($cache[$currentPageId]))  {
            return $cache[$currentPageId];
        }
        if($pageId = \Buepro\Easyconf\Utility\GeneralUtility::getValue($currentPageId, $params)) {
            if(BackendUtility::getRecord('pages', $pageId, 'uid')) {
                return $cache[$currentPageId] = true;
            }
        };
        return $cache[$currentPageId] = false;
    }

    public static function doesPageNotExist($params, $pObj): bool
    {
        return !self::doesPageExist($params, $pObj);
    }

    public static function getPid($params): mixed
    {
        if($params['effectivePid'] ?? false) return $params['effectivePid'];
        $row = $params['record'] ?? $params['row'];
        if(($row['uid'] ?? false) && ($row['pid'] ?? false)) {
            return BackendUtility::getTSCpid('tx_news_domain_model_news', $row['uid'], $row['pid'])[0];
        }
        throw new Exception('pid could not be found',1707305656);
    }
}
