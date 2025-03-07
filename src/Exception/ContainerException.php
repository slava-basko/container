<?php

namespace SDI\Exception;

use Exception;

class ContainerException extends Exception
{
    /**
     * @return \SDI\Exception\ContainerException
     */
    public static function createOffsetUnsetNotAllowed(): ContainerException
    {
        return new ContainerException('Unset not allowed');
    }
}
