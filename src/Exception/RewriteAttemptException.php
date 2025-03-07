<?php

namespace SDI\Exception;

class RewriteAttemptException extends ContainerException
{
    /**
     * @param string $id
     * @return self
     */
    public static function createFromId(string $id): RewriteAttemptException
    {
        return new self("The resource '$id' already defined");
    }
}
