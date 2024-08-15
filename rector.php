<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\Config\RectorConfig;

/** @noinspection PhpUnhandledExceptionInspection */
return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets(
        php81: true
    )
    ->withPreparedSets(
        deadCode          : true,
        codeQuality       : true,
        codingStyle       : true,
        typeDeclarations  : true,
        privatization     : true,
        instanceOf        : true,
        earlyReturn       : true,
        strictBooleans    : true,
        phpunitCodeQuality: true,
        phpunit           : true,
    )
    ->withSkip([
        NewlineAfterStatementRector::class
    ]);
