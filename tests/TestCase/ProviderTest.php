<?php

namespace SDI\Tests\TestCase;

use PHPUnit\Framework\TestCase;
use SDI\Container;

class ProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->addProvider(new \SomeServiceProvider());

        $this->assertEquals(123, $container['from-service-provider']);
        $this->assertEquals(456, $container['from-service-provider-2']);
    }
}
