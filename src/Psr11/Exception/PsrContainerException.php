<?php

namespace SDI\Psr11\Exception;

use Psr\Container\ContainerExceptionInterface;
use SDI\Exception\ContainerException;

class PsrContainerException extends ContainerException implements ContainerExceptionInterface
{
}
