<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__ . '/packages/core/src',
        __DIR__ . '/packages/core/tests',
        __DIR__ . '/examples',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PHP8x2Migration' => true,
        '@PHP8x0Migration:risky' => true,
        '@PHPUnit10x0Migration:risky' => true,
        '@PER-CS' => true,

        'assign_null_coalescing_to_coalesce_equal' => false,
        'use_arrow_functions' => false,
        'declare_strict_types' => true,

        'trim_array_spaces' => true,
        'whitespace_after_comma_in_array' => [
            'ensure_single_space' => true,
        ],
        'no_trailing_comma_in_singleline' => true,

        'single_quote' => true,

        'fully_qualified_strict_types' => [
            'import_symbols' => true,
        ],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'no_leading_import_slash' => true,
        'no_unused_imports' => true,
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
            'sort_algorithm' => 'alpha',
        ],
        'single_import_per_statement' => [
            'group_to_single_imports' => false,
        ],
        'single_line_after_imports' => true,
        'blank_line_after_opening_tag' => true,
        'blank_line_between_import_groups' => true,
        'blank_lines_before_namespace' => true,
        'blank_line_after_namespace' => true,

        'php_unit_attributes' => [
            'keep_annotations' => false,
        ],
        'php_unit_construct' => [
            'assertions' => ['assertEquals', 'assertNotEquals', 'assertNotSame', 'assertSame'],
        ],
        'php_unit_data_provider_method_order' => [
            'placement' => 'after',
        ],
        'php_unit_data_provider_name' => [
            'prefix' => '',
            'suffix' => 'DataProvider',
        ],
        'php_unit_data_provider_static' => [
            'force' => true,
        ],
        'php_unit_test_case_static_method_calls' => [
            'call_type' => 'static',
        ],
        'php_unit_set_up_tear_down_visibility' => true,
        'php_unit_method_casing' => [
            'case' => 'camel_case',
        ],
        'php_unit_strict' => [
            'assertions' => ['assertAttributeEquals', 'assertAttributeNotEquals', 'assertEquals', 'assertNotEquals'],
        ],

        'no_useless_else' => true,
        'no_superfluous_elseif' => true,
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
        ],

        'is_null' => true,

        'phpdoc_array_type' => true,
        'no_empty_phpdoc' => true,
        'no_superfluous_phpdoc_tags' => [
            'remove_inheritdoc' => true,
            'allow_mixed' => true,
        ],
        'no_blank_lines_after_phpdoc' => true,
        'phpdoc_trim' => true,
        'phpdoc_to_comment' => [
            'allow_before_return_statement' => false,
            'ignored_tags' => ['var'],
        ],
        'phpdoc_align' => [
            'align' => 'left',
        ],
        'phpdoc_types_order' => [
            'sort_algorithm' => 'none',
            'null_adjustment' => 'always_last',
        ],

        'no_useless_return' => true,
        'method_chaining_indentation' => true,
        'binary_operator_spaces' => [
            'default' => 'single_space',
        ],
        'types_spaces' => [
            'space' => 'none',
        ],
    ])
    ->setRiskyAllowed(true)
    ->setIndent('    ')
    ->setLineEnding("\n")
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/.tools/cache/php-cs-fixer.json');
