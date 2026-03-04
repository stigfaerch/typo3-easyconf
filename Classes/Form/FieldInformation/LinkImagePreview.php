<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Form\FieldInformation;

use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LinkImagePreview extends AbstractNode
{

    /**
     * @inheritDoc
     */
    public function render(): array
    {
        $linkService = GeneralUtility::makeInstance(LinkService::class);
        if ($file = $linkService->resolve($this->data['parameterArray']['itemFormElValue'])['file'] ?? false) {
            if (GeneralUtility::inList('gif,jpg,jpeg,tif,tiff,bmp,png', strtolower($file->getExtension()))) {
                $thumbUrl = $file->process(\TYPO3\CMS\Core\Resource\ProcessedFile::CONTEXT_IMAGECROPSCALEMASK, [
                    'width' => '450m',
                    'height' => 300,
                    'additionalParameters' => '-quality 50'
                ])->getPublicUrl();
            } elseif (strtolower($file->getExtension()) === 'svg') {
                $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
                $fileObject = $resourceFactory->getFileObjectFromCombinedIdentifier($file->getCombinedIdentifier());
                if($fileObject !== null && $fileObject->getPublicUrl() !== null){
                    $thumbUrl = $GLOBALS['TYPO3_REQUEST']->getUri()->getScheme() . '://' . $GLOBALS['TYPO3_REQUEST']->getUri()->getHost() . urldecode($fileObject->getPublicUrl());
                }
            } else {
                $content = '';
            }
            if ($thumbUrl ?? false) {
                $content = '<div class="col-md-12"><div style="width: 450px; height:300px; background-color:#bbbbbb"><div style="width: 450px; height:300px; background-image: url(\'' . $thumbUrl . '\'); background-position: left center; background-size: contain;
                background-repeat: no-repeat;"/></div></div>';
            }
        }
        return ['html' => $content ?? ''];
    }
}
