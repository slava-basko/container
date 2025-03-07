<?php

namespace SDI;

use ArrayAccess;
use SDI\Exception\ContainerException;
use SDI\Exception\InvalidArgumentException;

/**
 * @implements ArrayAccess<string, mixed>
 */
class Container extends AbstractContainer implements ArrayAccess
{
    /**
     * @param array<non-empty-string, mixed> $definitions
     * @throws \SDI\Exception\InvalidArgumentException
     */
    public function __construct(array $definitions = [])
    {
        InvalidArgumentException::assertListOfNotEmptyStrings(array_keys($definitions), __METHOD__, 1);

        $this->definitions = $definitions;
    }

    /**
     * @param non-empty-string $offset
     * @param mixed $value
     * @return void
     * @throws \SDI\Exception\InvalidArgumentException
     * @throws \SDI\Exception\RewriteAttemptException
     */
    public function offsetSet($offset, $value): void
    {
        InvalidArgumentException::assertNotEmptyString($offset, __METHOD__, 1);

        $this->add($offset, $value);
    }

    /**
     * @param non-empty-string $offset
     * @return mixed
     * @throws \SDI\Exception\CircularDependencyException
     * @throws \SDI\Exception\InvalidArgumentException
     * @throws \SDI\Exception\NotFoundException
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        InvalidArgumentException::assertNotEmptyString($offset, __METHOD__, 1);

        return $this->get($offset);
    }

    /**
     * @param non-empty-string $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * @param non-empty-string $offset
     * @return void
     * @throws \SDI\Exception\ContainerException
     */
    public function offsetUnset($offset): void
    {
        throw ContainerException::createOffsetUnsetNotAllowed();
    }
}
