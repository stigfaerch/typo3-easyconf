<?php

namespace Buepro\Easyconf\Form\FieldInformation;

use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LinkImagePreview extends AbstractNode
{

    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->data['parameterArray']['itemFormElValue'];
        $linkService = GeneralUtility::makeInstance(LinkService::class);
        if($file = $linkService->resolve($this->data['parameterArray']['itemFormElValue'])['file'] ?? false) {

            if (GeneralUtility::inList('gif,jpg,jpeg,tif,tiff,bmp,png', strtolower($file->getExtension()))) {
                $thumb = $file->process(\TYPO3\CMS\Core\Resource\ProcessedFile::CONTEXT_IMAGECROPSCALEMASK, array(
                    'width' => '450m',
                    'height' => 300,
                    'additionalParameters' => '-quality 50'
                ))->getPublicUrl();
                $content = '<div class="col-md-12"><div style="width: 450px; height:300px; background-image: url(' . $thumb . '); background-position: left center; background-size: contain;
background-repeat: no-repeat;"/></div>';
            } else {
                $content = '';
            }


        };
        return ['html' => $content ?? ''];
    }
}
