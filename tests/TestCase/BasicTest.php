<?php

namespace SDI\Tests\TestCase;

use PHPUnit\Framework\TestCase;
use SDI\Container;
use SDI\Exception\NotFoundException;

class BasicTest extends TestCase
{
    public function testKeys()
    {
        $container = new Container();
        $container['k1'] = 'v1';
        $container['k2'] = function ($c) {
            return new \User();
        };
        $container['k3'] = null;

        $this->assertEquals(['k1', 'k2', 'k3'], $container->keys());
    }

    public function testKeyValue()
    {
        $container = new Container();
        $container['k1'] = 'v1';
        $container['k2'] = 123;

        $this->assertEquals('v1', $container['k1']);
        $this->assertEquals(123, $container['k2']);
    }

    public function testService()
    {
        $container = new Container();
        $container['id'] = 99;
        $container[\User::class] = function ($c) {
            $user = new \User();
            $user->id = $c['id'];
            return $user;
        };

        $user = $container[\User::class];
        $this->assertInstanceOf(\User::class, $user);
        $this->assertEquals(99, $user->id);

        $user2 = $container[\User::class];
        $this->assertInstanceOf(\User::class, $user2);
        $this->assertEquals(99, $user2->id);

        $this->assertNotSame($user, $user2);
    }

    public function testSharedService()
    {
        $container = new Container();
        $container['id'] = 99;
        $container['user'] = Container::share(function ($c) {
            $user = new \User();
            $user->id = $c['id'];
            return $user;
        });

        $user = $container['user'];
        $this->assertInstanceOf(\User::class, $user);
        $this->assertEquals(99, $user->id);

        $user2 = $container['user'];
        $this->assertInstanceOf(\User::class, $user2);
        $this->assertEquals(99, $user2->id);

        $this->assertSame($user, $user2);
    }

    public function testAddSharedService()
    {
        $container = new Container();
        $container['id'] = 99;
        $container->addShared('user', function ($c) {
            $user = new \User();
            $user->id = $c['id'];
            return $user;
        });

        $user = $container['user'];
        $this->assertInstanceOf(\User::class, $user);
        $this->assertEquals(99, $user->id);

        $user2 = $container['user'];
        $this->assertInstanceOf(\User::class, $user2);
        $this->assertEquals(99, $user2->id);

        $this->assertSame($user, $user2);
    }

    public function testAsIs()
    {
        $container = new Container();
        $container['fn'] = Container::asIs(function() {
            return rand(1, 10);
        });

        $this->assertInstanceOf(\Closure::class, $container['fn']);
        $this->assertContains($container['fn'](), range(1, 10));
    }

    public function testNotDefinedService()
    {
        $container = new Container();

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("The resource 'non-existed-service-id' was not found.");

        $container['non-existed-service-id'];
    }

    public function testConstructorInjection()
    {
        $definitions = ['key' => 'value'];
        $container = new Container($definitions);

        $this->assertSame($definitions['key'], $container['key']);
    }

    public function testGetHonorsNullValues()
    {
        $container = new Container();
        $container['abc'] = null;
        $this->assertNull($container['abc']);
    }

    public function testUnset()
    {
        $container = new Container();
        $container['key'] = 'value';
        $container['service'] = function () {
            return new \User();
        };

        unset($container['key'], $container['service']);
        $this->assertFalse(isset($container['key']));
        $this->assertFalse(isset($container['service']));
    }
}