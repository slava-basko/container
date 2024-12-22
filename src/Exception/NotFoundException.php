<?php

namespace SDI\Exception;

class NotFoundException extends ContainerException
{
    /**
     * @param string $id
     * @return self
     */
    public static function forId(string $id): NotFoundException
    {
        return new self("The resource '$id' was not found.");
    }
}
