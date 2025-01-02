<?php

namespace SDI\Psr11;

use Psr\Container\ContainerInterface;
use SDI\Container;
use SDI\Exception\ContainerException;
use SDI\Exception\NotFoundException;
use SDI\Psr11\Exception\PsrContainerException;
use SDI\Psr11\Exception\PsrNotFoundException;

class PsrContainer implements ContainerInterface
{
    /**
     * @var \SDI\Container
     */
    private $container;

    /**
     * @param \SDI\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $id
     * @return mixed
     * @throws \SDI\Psr11\Exception\PsrContainerException
     * @throws \SDI\Psr11\Exception\PsrNotFoundException
     */
    public function get(string $id)
    {
        try {
            return $this->container->offsetGet($id);
        } catch (NotFoundException $notFoundException) {
            throw new PsrNotFoundException(
                $notFoundException->getMessage(),
                $notFoundException->getCode(),
                $notFoundException
            );
        } catch (ContainerException $generalException) {
            throw new PsrContainerException(
                $generalException->getMessage(),
                $generalException->getCode(),
                $generalException
            );
        }
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return $this->container->offsetExists($id);
    }
}
