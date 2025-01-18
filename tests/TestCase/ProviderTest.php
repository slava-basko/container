<?php

namespace SDI\Tests\TestCase;

use PHPUnit\Framework\TestCase;
use SDI\Container;

class ProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $provider = new \SomeServiceProvider();
        $container->addProvider($provider);

        $this->assertEquals(123, $container['from-service-provider']);
    }
}
