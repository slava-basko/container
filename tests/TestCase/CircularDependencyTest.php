<?php

namespace SDI\Tests\TestCase;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use SDI\AutowireContainer;
use SDI\Container;
use SDI\Exception\CircularDependencyException;
use SDI\Psr11\PsrContainer;

class CircularDependencyTest extends TestCase
{
    public function testCircularDependency()
    {
        $container = new Container();
        $container[\A::class] = function ($c) {
            return new \A($c[\B::class]);
        };
        $container[\B::class] = function ($c) {
            return new \B($c[\A::class]);
        };

        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage('Circular dependency detected: A -> B -> A');

        $a = $container[\A::class];
    }

    public function testCircularDependencyViaSetter()
    {
        // The below example is valid from the PHP perspective.
        // $b = new B2();
        // $a = new A2($b);
        // $b->setA2(new A2($b));
        //
        // But it's not the case from the Container's point of view.
        //
        // Container should return a service/object when it is ready, but this is not achievable due to circular dependency.

        $container = new Container();
        $container[\A2::class] = function ($c) {
            return new \A2($c[\B2::class]);
        };
        $container[\B2::class] = function ($c) {
            $b = new \B2();
            $b->setA2($c[\A2::class]);

            return $b;
        };

        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage('Circular dependency detected: A2 -> B2 -> A2');

        $a = $container[\A2::class];
    }

    public function testCircularDependencyAutowire()
    {
        $container = new AutowireContainer();

        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage('Circular dependency detected: A -> B -> A');

        $a = $container[\A::class];
    }

    public function testCircularDependencyPsr()
    {
        $container = new AutowireContainer();
        $psrContainer = new PsrContainer($container);

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectExceptionMessage('Circular dependency detected: A -> B -> A');

        $a = $psrContainer->get(\A::class);
    }
}
