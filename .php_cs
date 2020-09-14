<?php
declare(strict_types=1);

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@PhpCsFixer' => true,
        '@PSR2' => true,
        '@Symfony' => true,
        'psr4' => true,
        'strict_param' => false,
        'array_syntax' => ['syntax' => 'short'],
        'declare_strict_types' => true,
        'yoda_style' => [  // Revert Yoda style
            'equal' => false,
            'identical' => false
        ],
        'phpdoc_add_missing_param_annotation' => [
            'only_untyped' => false
        ],
        'linebreak_after_opening_tag' => true,
        'blank_line_after_opening_tag' => false,
        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'return', 'throw', 'try']//, 'declare'
        ],
        'phpdoc_no_empty_return' => false,
        'phpdoc_order' => false, // ecs require alphabetical order
        'trailing_comma_in_multiline_array' => false,
        'phpdoc_align' => [
            'align' => 'left',
            'tags' => ['param', 'property', 'return', 'throws', 'type', 'var', 'method']
        ],
        'concat_space' => [
            'spacing' => 'one'
        ],
        'cast_spaces' => [
            'space' => 'none'
        ],
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'no_multi_line'
        ],
        'class_attributes_separation' => [
            'elements' => ['const', 'method', 'property']
        ],
        'return_assignment' => false,
        'blank_line_after_namespace' => true,
        'single_blank_line_before_namespace' => true,
        'no_blank_lines_after_class_opening' => true,
        'phpdoc_types_order' => ['sort_algorithm' => 'none', 'null_adjustment' => 'none'],
        'phpdoc_separation' => true,
        'final_class' => true,
        'ordered_class_elements' => ['sortAlgorithm' => 'alpha']
    ])
    ->setLineEnding("\n")
    ->setUsingCache(false)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude(__DIR__ . '/vendor')
            ->in(__DIR__)
    );
