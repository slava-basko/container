<?php

namespace SDI;

use SDI\Exception\CircularDependencyException;
use SDI\Exception\InvalidArgumentException;
use SDI\Exception\NotFoundException;
use SDI\Exception\RewriteAttemptException;

use function array_key_exists;
use function array_keys;
use function array_pop;
use function in_array;
use function is_callable;

abstract class AbstractContainer implements ContainerInterface
{
    /**
     * @var array<non-empty-string, mixed>
     */
    protected $definitions = [];

    /**
     * @var array<non-empty-string, mixed>
     */
    protected $sharedInstances = [];

    /**
     * @var array<non-empty-string, bool>
     */
    protected $sharedServices = [];

    /**
     * @var array<non-empty-string, array<non-empty-string>>
     */
    protected $tags = [];

    /**
     * @var array<non-empty-string>
     */
    protected $resolvingStack = [];

    /**
     * @var array<non-empty-string, array<callable>>
     */
    protected $extenders = [];

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
        if (!$this->has($id)) {
            throw NotFoundException::createFromId($id);
        }

        if (isset($this->sharedInstances[$id])) {
            return $this->sharedInstances[$id];
        }

        $this->assertCircularDependency($id);
        $this->addIdToResolvingStack($id);

        if (is_callable($this->definitions[$id])) {
            $value = $this->definitions[$id]($this);
        } else {
            $value = $this->definitions[$id];
        }

        foreach ($this->extenders as $extenderId => $extenders) {
            foreach ($extenders as $extender) {
                if ($id === $extenderId || $value instanceof $extenderId) {
                    $value = $extender($value, $this);
                }
            }
        }

        $this->removeIdFromResolvingStack();

        if (isset($this->sharedServices[$id]) && !array_key_exists($id, $this->sharedInstances)) {
            $this->sharedInstances[$id] = $value;
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->definitions);
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
     * @inheritdoc
     */
    public function add(string $id, $value, array $tags = [])
    {
        InvalidArgumentException::assertNotEmptyString($id, __METHOD__, 1);

        if ($this->has($id)) {
            throw RewriteAttemptException::createFromId($id);
        }

        $this->definitions[$id] = $value;

        if (!empty($tags)) {
            InvalidArgumentException::assertListOfNotEmptyStrings($tags, __METHOD__, 3);
            $this->tags[$id] = $tags;
        }
    }

    /**
     * @inheritdoc
     */
    public function addShared(string $id, callable $value, array $tags = [])
    {
        $this->add($id, $value, $tags);
        $this->sharedServices[$id] = true;
    }

    /**
     * @inheritdoc
     */
    public function extend(string $id, callable $callable)
    {
        InvalidArgumentException::assertNotEmptyString($id, __METHOD__, 1);

        $this->extenders[$id][] = $callable;
    }

    /**
     * @param \SDI\ProviderInterface $provider
     * @return void
     */
    public function addProvider(ProviderInterface $provider)
    {
        $provider->register($this);
    }

    /**
     * @inheritdoc
     */
    public function get(string $id)
    {
        return $this->resolve($id);
    }

    /**
     * @inheritdoc
     */
    public function getByTag(string $tag): array
    {
        InvalidArgumentException::assertNotEmptyString($tag, __METHOD__, 1);

        $services = [];
        foreach ($this->definitions as $id => $definition) {
            if (isset($this->tags[$id]) && in_array($tag, $this->tags[$id], true)) {
                $services[] = $this->resolve($id);
            }
        }

        if (empty($services)) {
            throw NotFoundException::createFromId($tag);
        }

        return $services;
    }
}
