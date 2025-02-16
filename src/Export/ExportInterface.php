<?php

namespace SDI\Export;

interface ExportInterface
{
    /**
     * @return string
     */
    public function build(): string;
}
