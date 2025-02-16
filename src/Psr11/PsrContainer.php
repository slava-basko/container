<?php

namespace SDI\Psr11;

use Psr\Container\ContainerInterface;
use SDI\ContainerInterface as InternalContainer;
use SDI\Exception\ContainerException;
use SDI\Exception\NotFoundException;
use SDI\Psr11\Exception\PsrContainerException;
use SDI\Psr11\Exception\PsrNotFoundException;

class PsrContainer implements ContainerInterface
{
    /**
     * @var InternalContainer
     */
    private $container;

    /**
     * @param InternalContainer $container
     */
    public function __construct(InternalContainer $container)
    {
        $this->container = $container;
    }

    /**
     * @param non-empty-string $id
     * @return mixed
     * @throws \SDI\Psr11\Exception\PsrContainerException
     * @throws \SDI\Psr11\Exception\PsrNotFoundException
     */
    public function get(string $id)
    {
        try {
            return $this->container->get($id);
        } catch (NotFoundException $notFoundException) {
            throw PsrNotFoundException::createFromNotFoundException($notFoundException);
        } catch (ContainerException $generalException) {
            throw PsrContainerException::createFromContainerException($generalException);
        }
    }

    /**
     * @param non-empty-string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return $this->container->has($id);
    }
}
