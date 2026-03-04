<?php
declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Controller;

use Buepro\Easyconf\Mapper\Service\SiteConfigurationService;
use Buepro\Easyconf\Mapper\Service\SiteSettingsService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class AjaxFormController
{

    public function createPage(ServerRequestInterface $request): ResponseInterface
    {
        $parsedBody = (array)$request->getParsedBody();
        $targetPid = $parsedBody['targetPid']
            ?? throw new \InvalidArgumentException(
                'Please provide a number for targetPid.',
                1580585107,
            );
        $newPageTitle = $parsedBody['newPageTitle'];

        $newPidSettingPath = $parsedBody['newPidSettingPath'];

        if ($copySourcePid = $parsedBody['copySourcePid'] ?? false) {
            if ((bool)$newPageUid = $this->createNewPageFromCopy($copySourcePid, $targetPid, $newPageTitle)) {
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
        }

        $responseFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ResponseFactoryInterface::class);
        $response = $responseFactory->createResponse()
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write(
            json_encode(['result' => $newPageUid ?? '---'], JSON_THROW_ON_ERROR),
        );
        return $response;
    }

    private function createNewPageFromCopy(int $sourcePid, int $targetPid, string $newTitle = null): int
    {
        $adminBeUser = \Buepro\Easyconf\Utility\GeneralUtility::initAdmin((int)$GLOBALS['BE_USER']->user['uid']);
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

        if (count($dataHandler->errorLog) > 0) {
            $copyMapping = $dataHandler->copyMappingArray_merged['pages'];
            $newPageUid = $copyMapping[$sourcePid] ?? null;

            if ($newTitle !== null && $newTitle !== '') {
                $data['pages'][$newPageUid]['title'] = $newTitle;
            }
            $data['pages'][$newPageUid]['doktype'] = 254;
            $dataHandler->start($data, []);
            $dataHandler->process_datamap();
            $dataHandler->process_cmdmap();
        }

        $GLOBALS['BE_USER']->uc['easyconf_pagetree_refresh'] = true;
        $GLOBALS['BE_USER']->writeUC();

        if ($newPageUid ?? false) {
            return $newPageUid;
        }
        return 0;
    }

    private function getDataHandler(): DataHandler
    {
        return GeneralUtility::makeInstance(DataHandler::class);
    }
}
