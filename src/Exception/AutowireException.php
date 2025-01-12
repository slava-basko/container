<?php

namespace SDI\Exception;

class AutowireException extends ContainerException
{
    /**
     * @param string $serviceId
     * @param string $parameterName
     * @return \SDI\Exception\AutowireException
     */
    public static function createFromUnknownParamType(string $serviceId, string $parameterName): AutowireException
    {
        return new AutowireException(sprintf(
            'The type of parameter "$%s" of method %s can\'t be determined.',
            $parameterName,
            $serviceId . '::__construct()'
        ));
    }

    /**
     * @param string $serviceId
     * @param string $parameterName
     * @return \SDI\Exception\ContainerException
     */
    public static function createFromUnknownClass(string $serviceId, string $parameterName): ContainerException
    {
        return new ContainerException(sprintf(
            'The type of parameter "$%s" of method %s is not resolvable.',
            $parameterName,
            $serviceId . '::__construct()'
        ));
    }
}
