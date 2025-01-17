<?php

namespace SDI\Tests\TestCase;

use DbConnection;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionObject;
use SDI\Container;

class LazyTest extends TestCase
{
    protected function setUp(): void
    {
        if (PHP_VERSION_ID < 80400) {
            $this->markTestSkipped(
                'PHP >= 8.4 required'
            );
        }
    }

    public function testLazy()
    {
        $container = new Container();
        $container[DbConnection::class] = function (Container $c) {
            return (new ReflectionClass(DbConnection::class))->newLazyGhost(function (DbConnection $dbService) {
                return $dbService->__construct('mysql:host=example.com');
            });
        };

        $db = $container[DbConnection::class];

        $this->assertInstanceOf(\DbConnection::class, $db);
        $this->assertTrue((new ReflectionObject($db))->getProperty('connectionString')->isLazy($db));

        $a = $db->executeQuery('SELECT 1');

        $this->assertFalse((new ReflectionObject($db))->getProperty('connectionString')->isLazy($db));
        $this->assertEquals('mysql:host=example.com', $db->connectionString);
    }
}
