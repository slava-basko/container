<?php

namespace SDI\Psr11\Exception;

use Psr\Container\ContainerExceptionInterface;
use SDI\Exception\ContainerException;

class PsrContainerException extends ContainerException implements ContainerExceptionInterface
{
    /**
     * @param \SDI\Exception\ContainerException $containerException
     * @return \SDI\Psr11\Exception\PsrContainerException
     */
    public static function createFromContainerException(ContainerException $containerException): PsrContainerException
    {
        return new PsrContainerException(
            $containerException->getMessage(),
            $containerException->getCode(),
            $containerException
        );
    }
}
