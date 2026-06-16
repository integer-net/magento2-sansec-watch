<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\FunctionNotation\NativeFunctionInvocationFixer;
use PhpCsFixer\Fixer\Import\GlobalNamespaceImportFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withPhpCsFixerSets(
        perCS30: true,
    )
    ->withConfiguredRule(
        NativeFunctionInvocationFixer::class,
        [
            'include' => ['@all'],
        ],
    )
    ->withConfiguredRule(
        GlobalNamespaceImportFixer::class,
        [
            'import_classes'   => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
    );
