<?php

namespace SDI;

interface ProviderInterface
{
    public function register(Container $container): void;
}
