<?php

namespace SDI\Psr11\Exception;

use Psr\Container\NotFoundExceptionInterface;
use SDI\Exception\NotFoundException;

class PsrNotFoundException extends NotFoundException implements NotFoundExceptionInterface
{
    /**
     * @param \SDI\Exception\NotFoundException $notFoundException
     * @return \SDI\Psr11\Exception\PsrNotFoundException
     */
    public static function createFromNotFoundException(NotFoundException $notFoundException): PsrNotFoundException
    {
        return new PsrNotFoundException(
            $notFoundException->getMessage(),
            $notFoundException->getCode(),
            $notFoundException
        );
    }
}
