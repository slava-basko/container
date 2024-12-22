<?php

namespace Psr\Container;

if (!class_exists('\Psr\Container\ContainerInterface', false)) {
    interface ContainerInterface {}
}

if (!class_exists('\Psr\Container\ContainerExceptionInterface', false)) {
    interface ContainerExceptionInterface {}
}

if (!class_exists('\Psr\Container\NotFoundExceptionInterface', false)) {
    interface NotFoundExceptionInterface {}
}
