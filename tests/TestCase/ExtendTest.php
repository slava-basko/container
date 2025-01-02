<?php

namespace SDI\Tests\TestCase;

use PHPUnit\Framework\TestCase;
use SDI\AutowireContainer;
use SDI\Container;

class ExtendTest extends TestCase
{
    public function testExtendNoAffectToNonCallable()
    {
        $container = new Container();
        $container['k'] = 2;
        $container->extend('k', function ($n) {
            return $n * 2;
        });
        $this->assertEquals(2, $container['k']);
    }

    public function testSimpleExtend()
    {
        $container = new Container();
        $container['user'] = function ($c) {
            return new \User();
        };

        $user = $container['user'];
        $this->assertInstanceOf('\User', $user);
        $this->assertNull($user->id);

        $container->extend('user', function (\User $user, Container $c) {
            $user->id = 123;

            return $user;
        });
        $updatedUser = $container['user'];
        $this->assertEquals(123, $updatedUser->id);
    }

    public function testExtendShared()
    {
        $container = new Container();
        $container['user'] = Container::share(function ($c) {
            return new \User();
        });

        $user = $container['user'];
        $this->assertInstanceOf('\User', $user);
        $this->assertNull($user->id);

        $container->extend('user', function (\User $user) {
            $user->id = 123;

            return $user;
        });
        $updatedUser = $container['user'];
        $this->assertEquals(123, $updatedUser->id);

        $this->assertSame($user, $updatedUser);
    }

    public function testInheritanceExtend()
    {
        $container = new Container();
        $container[\Logger::class] = function () {
            return new \Logger();
        };
        $container[\SomeRepository::class] = function () {
            return new \SomeRepository();
        };
        $container->extend(\BaseRepository::class, function (\BaseRepository $repository, Container $c) {
            $repository->setLogger($c[\Logger::class]);

            return $repository;
        });

        $repository = $container[\SomeRepository::class];
        $this->assertInstanceOf(\SomeRepository::class, $repository);
        $this->assertInstanceOf(\Logger::class, $repository->logger);
    }

    public function testExtendNoAffectOnSimpleValues()
    {
        $container = new Container();
        $container['k'] = 'v';
        $container->extend(\BaseRepository::class, function (\BaseRepository $repository, Container $c) {
            $repository->setLogger($c[\Logger::class]);

            return $repository;
        });

        $this->assertEquals('v', $container['k']);
    }

    public function testAutowireExtend()
    {
        $container = new AutowireContainer();
        $container->extend(\BaseRepository::class, function (\BaseRepository $repository, Container $c) {
            $repository->setLogger($c[\Logger::class]);

            return $repository;
        });

        $repository = $container[\SomeRepository::class];
        $this->assertInstanceOf(\SomeRepository::class, $repository);
        $this->assertInstanceOf(\Logger::class, $repository->logger);
    }
}
