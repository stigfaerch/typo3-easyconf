<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Mapper;

use Buepro\Easyconf\Data\PropertyFieldMap;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractMapper implements MapperInterface
{
    protected array $buffer = [];

    private PropertyFieldMap $propertyFieldMap;

    public function __construct()
    {
        MapperRegistry::registerInstance($this);
    }

    public function injectPropertyFieldMap(PropertyFieldMap $propertyFieldMap): void
    {
        $this->propertyFieldMap = $propertyFieldMap;
    }

    public function getPropertyFieldMap(): PropertyFieldMap
    {
        return $this->propertyFieldMap;
    }

    public function bufferProperty(string $path, $value): MapperInterface
    {
        $this->buffer[$path] = $value;
        return $this;
    }

    public function removePropertyFromBuffer(string $path): MapperInterface
    {
        unset($this->buffer[$path]);
        return $this;
    }

    public function processFuncAfterBufferPersisted(int $pid): array
    {
        $buffers = is_array(reset($this->buffer)) ? $this->buffer : [$this->buffer];
        foreach ($buffers as $buffer) {
            foreach ($buffer as $path => $value) {
                $config = $this->getPropertyFieldMap()->getConfigurationFromPropertyPath($path);
                if ($config !== []) {
                    if ($config['postProcess'] ?? false) {
                        $postProcess = (is_array(reset($config['postProcess']))) ? $config['postProcess'] : [$config['postProcess']];
                        foreach ($postProcess as $arguments) {
                            $className = array_shift($arguments);
                            if (is_string($className) && class_exists($className)) {
                                /** @var class-string<object> $className */
                                GeneralUtility::makeInstance($className, $config, $this, $pid);
                            }
                        }
                    }
                }
            }
        }
        return $this->buffer;
    }
}
