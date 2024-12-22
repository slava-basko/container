<?php

namespace SDI\Psr11\Exception;

use Psr\Container\NotFoundExceptionInterface;
use SDI\Exception\NotFoundException;

class PsrNotFoundException extends NotFoundException implements NotFoundExceptionInterface
{
}
