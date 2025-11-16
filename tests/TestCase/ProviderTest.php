<?php

namespace SDI\Tests\TestCase;

use PHPUnit\Framework\TestCase;
use SDI\Container;
use SDI\ContainerInterface;

class ProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->addProvider(new \SomeServiceProvider());
        $container->addProvider(function (ContainerInterface $container) {
            $container['from-service-provider-3'] = 789;
        });

        $this->assertEquals(123, $container['from-service-provider']);
        $this->assertEquals(456, $container['from-service-provider-2']);
        $this->assertEquals(789, $container['from-service-provider-3']);
    }
}
