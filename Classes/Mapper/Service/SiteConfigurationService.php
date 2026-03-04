<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Mapper\Service;

use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SiteConfigurationService extends AbstractSiteConfigurationService
{

    public function load(): array
    {
        if ($this->getSite() !== null) {
            $fileName = $this->configPath . '/' . $this->getSite()->getIdentifier() . '/' . $this->configFileName;
            $loader = GeneralUtility::makeInstance(YamlFileLoader::class);
            return $loader->load(GeneralUtility::fixWindowsFilePath($fileName), 0);
        }
        throw new Exception('Site configuration service requires a valid site to load configuration', 1772553122);
    }

    public function write(array $siteData): void
    {
        if ($this->getSite() !== null) {
            $this->siteWriter->write($this->getSite()->getIdentifier(), $siteData);
        }
    }
}
