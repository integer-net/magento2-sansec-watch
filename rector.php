<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withComposerBased(
        phpunit: true,
    )
    ->withPhpLevel(PhpVersion::PHP_83)
    ->withSkip([
        ClosureToArrowFunctionRector::class,
    ]);
