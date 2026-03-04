<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Utility;

use Buepro\Easyconf\Utility\GeneralUtility as EasyconfGeneralUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\Exception\MissingArrayPathException;
use TYPO3\CMS\Core\Utility\GeneralUtility as CoreGeneralUtility;

class GeneralUtility
{
    public static function flushPagesCache(int $pageId): void
    {
        $config = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get('easyconf');
        if (is_array($config) && ($config['addAndUseSiteIdentifierPageCacheTag'] ?? false)) {
            try {
                $site = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pageId);
                \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(CacheManager::class)->getCache('typoscript')->flush();
                \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(CacheManager::class)->flushCachesInGroupByTag('pages', 'siteIdentifier_' . $site->getIdentifier());
            } catch (SiteNotFoundException $e) {
            }
        } else {
            \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(CacheManager::class)->flushCachesInGroup('pages');
        }
    }

    /**
     * @return string Relative path in the form "relative/path/" or ""
     */
    public static function trimRelativePath(string $path): string
    {
        $result = trim($path, " \t\n\r\0\x0B/") . '/';
        return $result === '/' ? '' : $result;
    }

    public static function convertToUnixLineBreaks(string $text): string
    {
        return str_replace(CR, '', $text);
    }

    public static function convertToWindowsLineBreaks(string $text): string
    {
        return str_replace(LF, CRLF, self::convertToUnixLineBreaks($text));
    }

    public static function readTextFile(string $fileName): string
    {
        $content = @file_get_contents($fileName);
        return is_string($content) ? self::convertToUnixLineBreaks($content) : '';
    }

    /**
     * Ensures the path to the file to be written exists and writes
     * the content with windows type line breaks to the file.
     *
     * @see GeneralUtility::writeFile()
     */
    public static function writeTextFile(string $fileName, string $content, bool $changePermissions = false): bool
    {
        $dir = CoreGeneralUtility::dirname($fileName);
        if (!file_exists($dir)) {
            CoreGeneralUtility::mkdir_deep($dir);
        }
        return CoreGeneralUtility::writeFile(
            $fileName,
            EasyconfGeneralUtility::convertToWindowsLineBreaks($content),
            $changePermissions
        );
    }

    public static function initAdmin(int $userIdToSet): BackendUserAuthentication
    {
        $newBeUser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(BackendUserAuthentication::class); // New backend user object
        $newBeUser->setBeUserByUid($userIdToSet);
        $newBeUser->fetchGroupData();

        // setting the user to admin rights temporarily during copy. The reason is that everything must be copied fully!
        $newBeUser->user['admin'] = 1;
        return $newBeUser;
    }

    public static function getValue(int $currentPageId, mixed $valueData): mixed
    {
        if (is_string($valueData)) {
            // When $valueData is a string but does not contain a semicolon
            if (!str_contains($valueData, ':')) {
                return $valueData;
                // When used in the PagesService class
            } else {
                $params = explode(':', $valueData);
                $source = $params[0];
                $key = $params[1];
            }
            // When used with displayCond. For example DisplayConditionFunctions->doesPageNotExist:site-configuration:pids.secondary-menu
        } elseif (is_array($valueData)) {
            $source = $valueData['conditionParameters'][0];
            $key = $valueData['conditionParameters'][1];
        } else {
            return null;
        }

        $site = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($currentPageId);
        switch ($source) {
            case 'site-settings':
                return $site->getSettings()->get($key, null);
            case 'site-configuration':
                try {
                    return ArrayUtility::getValueByPath($site->getConfiguration(), $key, '.');
                } catch (MissingArrayPathException $exception) {
                }
                return null;
            default:
                return throw new Exception(
                    'conditionParameters[0] must be either site-settings or site-configuration',
                    1707305657
                );
        }
    }
}
