<?php

$header = <<<EOF
This file is part of richardhj/contao-theme-framework.

(c) Richard Henkenjohann <richardhenkenjohann@googlemail.com>

@license MIT
EOF;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHPUnit60Migration:risky' => true,
        'align_multiline_comment' => true,
        'array_indentation' => true,
        'array_syntax' => ['syntax' => 'short'],
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'comment_to_phpdoc' => true,
        'compact_nullable_typehint' => true,
        'declare_strict_types' => true,
        'header_comment' => ['header' => $header],
        'heredoc_to_nowdoc' => true,
        'linebreak_after_opening_tag' => true,
        'native_function_invocation' => [
            'include' => ['@compiler_optimized'],
        ],
        'no_null_property_initialization' => true,
        'no_superfluous_elseif' => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => true,
        'php_unit_strict' => true,
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_order' => true,
        'phpdoc_types_order' => [
            'null_adjustment' => 'always_last',
            'sort_algorithm' => 'none',
        ],
        'strict_comparison' => true,
        'strict_param' => true,
    ])
    ->setFinder((new PhpCsFixer\Finder())->in([__DIR__.'/src']))
    ->setRiskyAllowed(true)
    ->setUsingCache(false)
;
