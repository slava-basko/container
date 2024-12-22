<?php

namespace SDI\Exception;

use function get_class;
use function gettype;
use function is_array;
use function is_object;
use function is_string;
use function sprintf;

class InvalidArgumentException extends ContainerException
{
    /**
     * @param mixed $list
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws \SDI\Exception\InvalidArgumentException
     */
    public static function assertList($list, string $callee, int $parameterPosition)
    {
        if (!is_array($list)) {
            throw new self(
                sprintf(
                    '%s() expects parameter %d to be array, %s given',
                    $callee,
                    $parameterPosition,
                    self::getType($list)
                )
            );
        }
    }

    /**
     * @param mixed $list
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws static
     */
    public static function assertListOfStrings($list, string $callee, int $parameterPosition)
    {
        static::assertList($list, $callee, $parameterPosition);

        /** @var array<string> $list */
        foreach ($list as $item) {
            static::assertString($item, $callee, $parameterPosition);
        }
    }

    /**
     * @param mixed $value
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws static
     */
    public static function assertString($value, string $callee, int $parameterPosition)
    {
        if (!is_string($value)) {
            throw new self(
                sprintf(
                    '%s() expects parameter %d to be string, %s given',
                    $callee,
                    $parameterPosition,
                    self::getType($value)
                )
            );
        }
    }

    /**
     * @param mixed $value
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws self
     */
    public static function assertNotEmptyString($value, string $callee, int $parameterPosition)
    {
        static::assertString($value, $callee, $parameterPosition);

        if ($value == '') {
            throw new self(
                sprintf(
                    '%s() expects parameter %d to be non-empty-string, empty %s given',
                    $callee,
                    $parameterPosition,
                    self::getType($value)
                )
            );
        }
    }

    /**
     * @param mixed $value
     * @return string
     */
    private static function getType($value): string
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }
}
