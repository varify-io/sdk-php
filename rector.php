<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php80\Rector\Ternary\TernaryToNullsafeCoalesceRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;
use Rector\PHPUnit\Set\PHPUnitSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/packages/core/src',
        __DIR__ . '/packages/core/tests',
        __DIR__ . '/packages/shopware/src',
        __DIR__ . '/packages/shopware/tests',
        __DIR__ . '/examples',
    ])
    ->withCache(__DIR__ . '/.tools/cache/rector')
    ->withPhpSets()
    ->withSets([
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
    ])
    ->withImportNames()
    ->withSkip([
        __DIR__ . '/examples/shopware-app/project',
        ClosureToArrowFunctionRector::class,
        PreferPHPUnitThisCallRector::class,
        // Conflicts with PHPStan, as in laravel-spark.
        TernaryToNullsafeCoalesceRector::class,
    ]);
