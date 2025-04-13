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

    /**
     * @return \SDI\Exception\ContainerException
     */
    public static function createIdEqualSymlink(): ContainerException
    {
        return new ContainerException('The symlink can not be identical to id');
    }
}
