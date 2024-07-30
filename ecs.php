<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withPreparedSets(
        psr12: true,
        symplify: true,
        arrays: true,
        comments: true,
        spaces: true,
        namespaces: true,
        controlStructures: true,
        strict: true,
        cleanCode: true,
    )
    ->withSkip([
        BinaryOperatorSpacesFixer::class,
        GeneralPhpdocAnnotationRemoveFixer::class,
        LineLengthFixer::class,
        NotOperatorWithSuccessorSpaceFixer::class,
    ]);
