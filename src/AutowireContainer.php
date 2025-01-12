<?php

namespace SDI;

use ReflectionClass;
use ReflectionNamedType;
use SDI\Exception\AutowireException;

use function class_exists;

class AutowireContainer extends Container
{
    /**
     * @param string $offset
     * @return array|mixed
     * @throws \SDI\Exception\CircularDependencyException
     * @throws \SDI\Exception\ContainerException
     * @throws \SDI\Exception\InvalidArgumentException
     * @throws \SDI\Exception\NotFoundException
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            $this->define($offset);
        }

        return parent::offsetGet($offset);
    }

    /**
     * @param string $id
     * @return void
     * @throws \SDI\Exception\CircularDependencyException
     * @throws \SDI\Exception\ContainerException
     */
    private function define(string $id)
    {
        if (!class_exists($id)) {
            return;
        }

        $this->assertCircularDependency($id);
        $this->addIdToResolvingStack($id);

        $constructorArgs = [];
        $reflection = new ReflectionClass($id);
        $constructor = $reflection->getConstructor();
        if ($constructor) {
            /** @var array<\ReflectionParameter> $constructorParams */
            $constructorParams = $constructor->getParameters();
            foreach ($constructorParams as $constructorParam) {
                if ($constructorParam->isOptional()) {
                    continue;
                }

                $parameterName = $constructorParam->getName();
                $parameterType = $constructorParam->getType();
                if (!$parameterType instanceof ReflectionNamedType) {
                    throw AutowireException::createFromUnknownParamType($id, $parameterName);
                }

                $param = $parameterType->getName();

                if (!class_exists($param)) {
                    throw AutowireException::createFromUnknownClass($id, $parameterName);
                }

                if (!$this->offsetExists($param)) {
                    $this->define($param);
                }

                $constructorArgs[] = $param;
            }
        }

        $this[$id] = function (Container $c) use ($id, $constructorArgs) {
            $args = [];
            foreach ($constructorArgs as $constructorArg) {
                $args[] = $c[$constructorArg];
            }
            return new $id(...$args);
        };

        $this->removeIdFromResolvingStack();
    }
}
