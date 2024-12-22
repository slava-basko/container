<?php

namespace SDI\Tests\TestCase;

use PHPUnit\Framework\TestCase;
use SDI\Container;

class TagsTest extends TestCase
{
    public function testTags()
    {
        $container = new Container();
        $container['k'] = 'v';
        $container->add(\C::class, function () {
            return new \C();
        }, ['some-tag']);
        $container->add(\D::class, function () {
            return new \D();
        }, ['some-tag']);
        $container->add(\E::class, function () {
            return new \E();
        });
        $container->add(\F::class, function () {
            return new \F();
        }, ['some-another-tag']);

        $services = $container['some-tag'];

        $this->assertIsArray($services);
        $this->assertCount(2, $services);
        $this->assertInstanceOf(\C::class, $services[0]);
        $this->assertInstanceOf(\D::class, $services[1]);
    }
}