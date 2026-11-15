<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withoutParallel()
    ->withPaths([
        __DIR__ . '/src/Controller',
    ])
    ->withAttributesSets(symfony: true);
