<?php

namespace SDI;

interface ProviderInterface
{
    public function register(ContainerInterface $container): void;
}
