<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Mapper\Service;

use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Configuration\Exception\SiteConfigurationWriteException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SiteSettingsService extends AbstractSiteConfigurationService
{

    public function load(): array
    {

        if($this->getSite() !== null) {
            $site = $this->siteConfiguration->resolveAllExistingSites(false)[$this->getSite()->getIdentifier()] ?? null;
            if ($site !== null) {
                return $site->getSettings()->getAll();
            }
        }
        /** @var Site|null $site */
        return [];
    }

    public function write(array $siteData): void
    {
        if($this->getSite() !== null){
            $yamlFileContents = Yaml::dump($siteData, 99, 2);
            if (!GeneralUtility::writeFile($this->configPath . '/' . $this->getSite()->getIdentifier() . '/settings.yaml', $yamlFileContents, true)) {
                throw new SiteConfigurationWriteException('Unable to write site settings in sites/' . $this->getSite()->getIdentifier() . '/' . 'settings.yaml', 1590487012);
            }

            // Invalidate the specific cache entry for this site's settings
            $this->invalidateSiteSettingsCache();
        }
    }

    private function invalidateSiteSettingsCache(): void
    {
        $site = $this->getSite();
        if ($site === null) {
            return;
        }

        // Get the cache and cache identifier generator
        $cacheManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Cache\CacheManager::class);
        $cache = $cacheManager->getCache('core');
        $packageDependentCacheIdentifier = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Package\Cache\PackageDependentCacheIdentifier::class
        )->withPrefix('SiteSettings');

        // Get the site configuration to generate the correct cache identifier
        $siteConfiguration = $site->getConfiguration();
        unset($siteConfiguration['settings'], $siteConfiguration['contentSecurityPolicies']);
        $cacheIdentifier = $packageDependentCacheIdentifier->withAdditionalHashedIdentifier(
            $site->getIdentifier() . '_' . json_encode($siteConfiguration)
        )->toString();

        // Remove only this specific cache entry
        $cache->remove($cacheIdentifier);
    }
}
