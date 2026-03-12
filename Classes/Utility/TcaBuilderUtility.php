<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Utility;

class TcaBuilderUtility
{
    private static ?array $headerTagConfig = [];

    public static function setHeaderTagConfig(string $tag, ?array $additionalAttributes = null): void
    {
        self::$headerTagConfig = self::getHeaderTagConfigFromArray([$tag, $additionalAttributes]);
    }

    public static function getHeaderTagConfig(): ?array
    {
        return self::$headerTagConfig;
    }

    public static function getHeaderTagConfigFromArray(array $config): array
    {
        $tag = $config[0] ?? $config['tag'] ?? null;
        $additionalAttributes = $config[1] ?? $config['additionalAttributes'] ?? null;
        return ['tag' => $tag, 'additionalAttributes' => $additionalAttributes];
    }

    public static function addClearCacheForSite(array $config): array
    {
        $config['clearCacheForSite'] = true;
        return $config;
    }

    public static function randomIdIfNull(?string $id = null): string
    {
        return is_null($id) ? bin2hex(random_bytes(5)) : $id;
    }

}
