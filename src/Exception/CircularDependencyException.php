<?php

namespace SDI\Exception;

use function implode;

class CircularDependencyException extends ContainerException
{
    /**
     * @param string $id
     * @param array<string> $resolvingStack
     * @return \SDI\Exception\CircularDependencyException
     */
    public static function createFromStack(string $id, array $resolvingStack): CircularDependencyException
    {
        $path = implode(' -> ', $resolvingStack);
        return new CircularDependencyException("Circular dependency detected: $path -> $id");
    }
}
