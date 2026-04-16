<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

return function (ContainerConfigurator $container, ContainerBuilder $containerBuilder) {
    $services = $container->services();

    // Set default configurations
    $services
        ->defaults()
        ->autowire(true)
        ->autoconfigure(true)
        ->private();

    // Configure services for the namespace Buepro\Easyconf
    $services
        ->load('Buepro\\Easyconf\\', '../Classes/*');

    $services
        ->set(\Buepro\Easyconf\Service\DatabaseService::class)
        ->public();
    $services
        ->set(\Buepro\Easyconf\Controller\AjaxFormController::class)
        ->public();
//    // Specific configuration for Buepro\Easyconf\Configuration\SiteConfiguration
//    $serviceConfigurator = $services
//        ->get(Buepro\Easyconf\Configuration\SiteConfiguration::class);
//    $serviceConfigurator->arg('$configPath', '%env(TYPO3:configPath)%/sites');

    $containerBuilder->registerForAutoconfiguration(\Buepro\Easyconf\EventListener\ExcludeFromIndexing::class)
        ->addTag('event.listener', [
            'identifier' => 'buepro/easyconf/exclude-from-indexing',
            'event' => \TYPO3\CMS\Core\DataHandling\Event\IsTableExcludedFromReferenceIndexEvent::class
        ]);
    $containerBuilder->registerForAutoconfiguration(\Buepro\Easyconf\EventListener\RefreshPageTree::class)
        ->addTag('event.listener', [
            'identifier' => 'buepro/easyconf/refresh-page-tree',
            'event' => 'TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent'
        ]);
};
