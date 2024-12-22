<?php

namespace SDI\Tests\TestCase;

use PHPUnit\Framework\TestCase;
use SDI\AutowireContainer;
use SDI\Container;
use SDI\Exception\CircularDependencyException;

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

    public function testCircularDependencyAutowire()
    {
        $container = new AutowireContainer();

        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage('Circular dependency detected: A -> B -> A');

        $a = $container[\A::class];
    }
}
