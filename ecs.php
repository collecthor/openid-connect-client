<?php

declare(strict_types=1);

// ecs.php
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    // Parallel
    $ecsConfig->parallel();

    // Paths
    $ecsConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/ecs.php'
    ]);
    // A. full sets
    $ecsConfig->import(SetList::PSR_12);


    // B. standalone rule
    $ecsConfig->rule(ArraySyntaxFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Import\NoUnusedImportsFixer::class);
};
