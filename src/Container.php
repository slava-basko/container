<?php

namespace SDI;

use ArrayAccess;
use SDI\Exception\CircularDependencyException;
use SDI\Exception\InvalidArgumentException;
use SDI\Exception\NotFoundException;

use function array_key_exists;
use function array_keys;
use function array_pop;
use function in_array;
use function is_callable;

/**
 * @implements ArrayAccess<string, mixed>
 */
class Container implements ArrayAccess
{
    /**
     * @var array<non-empty-string, mixed>
     */
    private $definitions;

    /**
     * @var array<non-empty-string, array<non-empty-string>>
     */
    private $tags = [];

    /**
     * @var array<non-empty-string>
     */
    private $resolvingStack = [];

    /**
     * @var array<non-empty-string, callable>
     */
    private $extenders = [];

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
     * Checking for cyclic dependency
     *
     * @param non-empty-string $id
     * @return void
     * @throws \SDI\Exception\CircularDependencyException
     */
    protected function assertCircularDependency(string $id): void
    {
        if (in_array($id, $this->resolvingStack, true)) {
            throw CircularDependencyException::createFromStack($id, $this->resolvingStack);
        }
    }

    /**
     * Adding the current dependency to the stack
     *
     * @param non-empty-string $id
     * @return void
     */
    protected function addIdToResolvingStack(string $id): void
    {
        $this->resolvingStack[] = $id;
    }

    /**
     * Removing a dependency from the stack
     *
     * @return void
     */
    protected function removeIdFromResolvingStack(): void
    {
        array_pop($this->resolvingStack);
    }

    /**
     * @param non-empty-string $id
     * @return mixed
     * @throws \SDI\Exception\CircularDependencyException
     * @throws \SDI\Exception\NotFoundException
     * @throws \SDI\Exception\InvalidArgumentException
     */
    private function resolve(string $id)
    {
        if (!$this->offsetExists($id)) {
            throw NotFoundException::createFromId($id);
        }

        $this->assertCircularDependency($id);
        $this->addIdToResolvingStack($id);

        // Resolve
        if (is_callable($this->definitions[$id])) {
            $value = $this->definitions[$id]($this);
        } else {
            $value = $this->definitions[$id];
        }

        foreach ($this->extenders as $extenderId => $extender) {
            if ($id === $extenderId || $value instanceof $extenderId) {
                $value = $extender($value, $this);
            }
        }

        $this->removeIdFromResolvingStack();

        return $value;
    }

    /**
     * @param non-empty-string $offset
     * @param mixed $value
     * @return void
     * @throws \SDI\Exception\InvalidArgumentException
     */
    public function offsetSet($offset, $value): void
    {
        InvalidArgumentException::assertNotEmptyString($offset, __METHOD__, 1);

        $this->definitions[$offset] = $value;
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

        /** @var non-empty-string $offset */
        return $this->resolve($offset);
    }

    /**
     * Alias for Container::offsetGet()
     *
     * @param non-empty-string $id
     * @return mixed
     * @throws \SDI\Exception\CircularDependencyException
     * @throws \SDI\Exception\InvalidArgumentException
     * @throws \SDI\Exception\NotFoundException
     */
    public function get(string $id)
    {
        return $this->offsetGet($id);
    }

    /**
     * @param non-empty-string $tag
     * @return non-empty-array<mixed>
     * @throws \SDI\Exception\CircularDependencyException
     * @throws \SDI\Exception\InvalidArgumentException
     * @throws \SDI\Exception\NotFoundException
     */
    public function getByTag(string $tag): array
    {
        InvalidArgumentException::assertNotEmptyString($tag, __METHOD__, 1);

        $services = [];
        foreach ($this->definitions as $id => $definition) {
            if (isset($this->tags[$id]) && in_array($tag, $this->tags[$id], true)) {
                /** @var non-empty-string $id */
                $services[] = $this->resolve($id);
            }
        }

        if (empty($services)) {
            throw NotFoundException::createFromId($tag);
        }

        /** @var non-empty-array<mixed> */
        return $services;
    }

    /**
     * @param non-empty-string $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->definitions);
    }

    /**
     * Alias for Container::offsetExists()
     *
     * @param non-empty-string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return $this->offsetExists($id);
    }

    /**
     * @param non-empty-string $offset
     * @return void
     * @throws \SDI\Exception\InvalidArgumentException
     */
    public function offsetUnset($offset): void
    {
        InvalidArgumentException::assertNotEmptyString($offset, __METHOD__, 1);

        unset($this->definitions[$offset], $this->tags[$offset], $this->extenders[$offset]);
    }

    /**
     * Returns all defined value names.
     *
     * @return array<non-empty-string>
     */
    public function keys(): array
    {
        return array_keys($this->definitions);
    }

    /**
     * Treat callable not as factory, but simply callable.
     *
     * @param callable $value
     * @return callable
     */
    public static function asIs(callable $value): callable
    {
        return function () use ($value) {
            return $value;
        };
    }

    /**
     * Wrap factory to this method to make service shareable.
     *
     * @param callable $value
     * @return callable
     */
    public static function share(callable $value): callable
    {
        return function (Container $c) use ($value) {
            static $object;

            if ($object === null) {
                $object = $value($c);
            }

            return $object;
        };
    }

    /**
     * @param non-empty-string $id
     * @param mixed $value
     * @param array<non-empty-string> $tags
     * @return void
     * @throws \SDI\Exception\InvalidArgumentException
     */
    public function add(string $id, $value, array $tags = [])
    {
        InvalidArgumentException::assertNotEmptyString($id, __METHOD__, 1);
        InvalidArgumentException::assertListOfNotEmptyStrings($tags, __METHOD__, 3);

        $this[$id] = $value;
        $this->tags[$id] = $tags;
    }

    /**
     * @param non-empty-string $id
     * @param callable $value
     * @param array<non-empty-string> $tags
     * @return void
     * @throws \SDI\Exception\InvalidArgumentException
     */
    public function addShared(string $id, callable $value, array $tags = [])
    {
        $this->add($id, $this::share($value), $tags);
    }

    /**
     * @param non-empty-string $id
     * @param callable $callable
     * @return void
     * @throws \SDI\Exception\InvalidArgumentException
     */
    public function extend(string $id, callable $callable)
    {
        InvalidArgumentException::assertNotEmptyString($id, __METHOD__, 1);

        $this->extenders[$id] = $callable;
    }

    /**
     * @param \SDI\ProviderInterface $provider
     * @return void
     */
    public function addProvider(ProviderInterface $provider)
    {
        $provider->register($this);
    }
}
