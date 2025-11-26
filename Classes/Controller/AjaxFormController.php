<?php
declare(strict_types=1);

namespace Buepro\Easyconf\Controller;

use Buepro\Easyconf\Mapper\Service\SiteConfigurationService;
use Buepro\Easyconf\Mapper\Service\SiteSettingsService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class AjaxFormController
{

    public function createPage(ServerRequestInterface $request): ResponseInterface
    {
        $targetPid = $request->getParsedBody()['targetPid']
            ?? throw new \InvalidArgumentException(
                'Please provide a number for targetPid.',
                1580585107,
            );
        $newPageTitle = $request->getParsedBody()['newPageTitle'];

        $newPidSettingPath = $request->getParsedBody()['newPidSettingPath'];


        if($copySourcePid = $request->getParsedBody()['copySourcePid'] ?? false) {
            if($newPageUid = $this->createNewPageFromCopy($copySourcePid, $targetPid, $newPageTitle)) {
                $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($newPageUid);

                list($configTarget, $path) = GeneralUtility::trimExplode(':', $newPidSettingPath);

                if ($configTarget === 'site-settings') {
                    $siteDataService = GeneralUtility::makeInstance(SiteSettingsService::class);

                } elseif ($configTarget === 'site-configuration') {
                    $siteDataService = GeneralUtility::makeInstance(SiteConfigurationService::class);
                } else {
                    throw new Exception('newPidSettingPath has wrong first value. Should either be site-settings org site-configuration');
                }
                $siteDataService->init($site->getRootPageId())->addAndSaveValue($path, $newPageUid);
            }
        };


        $responseFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ResponseFactoryInterface::class);
        $response = $responseFactory->createResponse()
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write(
            json_encode(['result' => $newPageUid ?? '---'], JSON_THROW_ON_ERROR),
        );
        return $response;
    }

    private function createNewPageFromCopy($sourcePid, $targetPid, $newTitle = null): int
    {
        $adminBeUser = \Buepro\Easyconf\Utility\GeneralUtility::initAdmin($GLOBALS['BE_USER']->user['uid']);
        $data = [];
        $cmd = [
            'pages' => [
                $sourcePid => [
                    'copy' => $targetPid,
                ],
            ],
        ];
        $dataHandler = $this->getDataHandler();

        $dataHandler->start($data, $cmd, $adminBeUser);
        $dataHandler->copyTree = 10;
        $dataHandler->process_datamap();
        $dataHandler->process_cmdmap();

        if (empty($dataHandler->errorLog)) {
            $copyMapping = $dataHandler->copyMappingArray_merged['pages'];
            $newPageUid = $copyMapping[$sourcePid] ?? null;

            if($newTitle) $data['pages'][$newPageUid]['title'] = $newTitle;
            $data['pages'][$newPageUid]['doktype'] = 254;
            $dataHandler->start($data, []);
            $dataHandler->process_datamap();
            $dataHandler->process_cmdmap();
        }

        GeneralUtility::makeInstance(Registry::class)->set('easyconf_pagetree', 'update', true);
        if($newPageUid ?? false) return $newPageUid;
        return 0;
    }

    private function getDataHandler()
    {
        return GeneralUtility::makeInstance(DataHandler::class);
    }
}
