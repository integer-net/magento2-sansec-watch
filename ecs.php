<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Basic\SingleLineEmptyBodyFixer;
use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withPhpCsFixerSets(
        perCS20: true,
    )
    ->withPreparedSets(
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
        CastSpacesFixer::class,
        ClassAttributesSeparationFixer::class,
        GeneralPhpdocAnnotationRemoveFixer::class,
        LineLengthFixer::class,
        NotOperatorWithSuccessorSpaceFixer::class,
        SingleLineEmptyBodyFixer::class,
    ]);
