<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

$EM_CONF[$_EXTKEY] = [
    'title'            => 'Easyconf',
    'description'      => 'Provides a module to easily configure main aspects from a website.',
    'category'         => 'module',
    'version'          => '3.0.0-dev',
    'state'            => 'stable',
    'clearCacheOnLoad' => 1,
    'author'           => 'Roman Büchler',
    'author_email'     => 'rb@buechler.pro',
    'autoload' => [
        'psr-4' => [
            'Buepro\\Easyconf\\' => 'Classes'
        ],
    ],
];
