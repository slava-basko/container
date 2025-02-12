<?php

namespace SDI\Tests\TestCase;

use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;
use SDI\Container;
use SDI\Psr11\PsrContainer;

class Psr11Test extends TestCase
{
    public function testService()
    {
        $container = new Container();
        $container['id'] = 99;
        $container[\User::class] = function ($c) {
            $user = new \User();
            $user->id = $c['id'];
            return $user;
        };

        $psrContainer = new PsrContainer($container);

        $this->assertTrue($psrContainer->has(\User::class));

        $user = $psrContainer->get(\User::class);
        $this->assertInstanceOf(\User::class, $user);
        $this->assertEquals(99, $user->id);
    }

    public function testNotFound()
    {
        $container = new Container();
        $psrContainer = new PsrContainer($container);
        $this->expectException(\Psr\Container\NotFoundExceptionInterface::class);
        $this->expectExceptionMessage("The resource 'User' was not found.");
        $psrContainer->get(\User::class);
    }
}
