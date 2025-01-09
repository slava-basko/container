<?php

namespace SDI\Tests\TestCase;

use G;
use H;
use I;
use PHPUnit\Framework\TestCase;
use SDI\AutowireContainer;
use SDI\Exception\ContainerException;
use SDI\Exception\NotFoundException;
use User;

class AutowireTest extends TestCase
{
    public function testAutowire()
    {
        $container = new AutowireContainer();

        $g = $container[G::class];
        $this->assertInstanceOf(G::class, $g);
        $this->assertInstanceOf(User::class, $g->client);
    }

    public function testAutowireAllNew()
    {
        $container = new AutowireContainer();
        $container[User::class] = function ($c) {
            return new User(true);
        };
        $this->assertNotEquals($container[G::class]->client->id, $container[G::class]->client->id);
    }

    public function testAutowireShareable()
    {
        $container = new AutowireContainer();
        $container[User::class] = $container->share(function ($c) {
            return new User(true);
        });
        $this->assertEquals($container[G::class]->client->id, $container[G::class]->client->id);
    }

    public function testAutowireNoType()
    {
        $container = new AutowireContainer();
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('The type of parameter "$someArg" of method H::__construct() can\'t be determined.');
        $h = $container[H::class];
    }

    public function testAutowireNotResolvableType()
    {
        $container = new AutowireContainer();
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('The type of parameter "$someArg2" of method I::__construct() is not resolvable.');
        $i = $container[I::class];
    }

    public function testAutowireNotExist()
    {
        $container = new AutowireContainer();
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("The resource 'some' was not found.");
        $container['some'];
    }
}
