<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

    // Specific configuration for Buepro\Easyconf\Configuration\SiteConfiguration
    $serviceConfigurator = $services
        ->get(Buepro\Easyconf\Configuration\SiteConfiguration::class);
    $serviceConfigurator->arg('$configPath', '%env(TYPO3:configPath)%/sites');

    $containerBuilder->registerForAutoconfiguration(\Buepro\Easyconf\EventListener\ExcludeFromIndexing::class)
        ->addTag('event.listener', [
            'identifier' => 'buepro/easyconf/exclude-from-indexing',
            'event' => \TYPO3\CMS\Core\DataHandling\Event\IsTableExcludedFromReferenceIndexEvent::class
        ]);

    if((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() == 12) {
        $serviceConfigurator->arg('$coreCache', new \Symfony\Component\DependencyInjection\Reference('cache.core'));
    }
};
