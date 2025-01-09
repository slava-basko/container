<?php

namespace SDI\Tests\TestCase;

use PHPUnit\Framework\TestCase;
use SDI\Container;
use SDI\Exception\InvalidArgumentException;

class ErrorsTest extends TestCase
{
    public function testInvalidTags()
    {
        $container = new Container();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Container::add() expects parameter 3 to be non-empty-array of strings, but one of element is integer');

        $container->add(\C::class, function () {
            return new \C();
        }, [123]);
    }

    public function testInvalidTagsEmptyString()
    {
        $container = new Container();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Container::add() expects parameter 3 to be non-empty-array of strings, but one of element is empty string');

        $container->add(\C::class, function () {
            return new \C();
        }, ['']);
    }

    public function testInvalidKey()
    {
        $container = new Container();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Container::offsetSet() expects parameter 1 to be string, integer given');

        $container[123] = 'value';
    }

    public function testInvalidKeyString()
    {
        $container = new Container();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Container::offsetSet() expects parameter 1 to be non-empty-string, empty string given');

        $container[''] = 'value';
    }
}
