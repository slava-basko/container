<?php

namespace SDI;

interface ContainerInterface
{
    /**
     * Alias for Container::offsetExists()
     *
     * @param non-empty-string $id
     * @return bool
     */
    public function has(string $id): bool;

    /**
     * @param non-empty-string $id
     * @param mixed $value
     * @param array<non-empty-string> $tags
     * @return void
     * @throws \SDI\Exception\InvalidArgumentException
     * @throws \SDI\Exception\RewriteAttemptException
     */
    public function add(string $id, $value, array $tags = []);

    /**
     * @param non-empty-string $id
     * @param callable $value
     * @param array<non-empty-string> $tags
     * @return void
     * @throws \SDI\Exception\InvalidArgumentException
     */
    public function addShared(string $id, callable $value, array $tags = []);

    /**
     * @param non-empty-string $id
     * @param callable $callable
     * @return void
     * @throws \SDI\Exception\InvalidArgumentException
     */
    public function extend(string $id, callable $callable);

    /**
     * Alias for Container::offsetGet()
     *
     * @param non-empty-string $id
     * @return mixed
     * @throws \SDI\Exception\CircularDependencyException
     * @throws \SDI\Exception\InvalidArgumentException
     * @throws \SDI\Exception\NotFoundException
     */
    public function get(string $id);

    /**
     * @param non-empty-string $tag
     * @return non-empty-array<mixed>
     * @throws \SDI\Exception\CircularDependencyException
     * @throws \SDI\Exception\InvalidArgumentException
     * @throws \SDI\Exception\NotFoundException
     */
    public function getByTag(string $tag): array;
}
