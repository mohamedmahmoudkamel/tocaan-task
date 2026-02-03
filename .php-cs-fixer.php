<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__)
    ->name('*.php')
    ->exclude('vendor')
    ->exclude('storage')
    ->exclude('bootstrap');

return (new Config())
    ->setRules([
        '@PSR2' => true,
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
            'sort_algorithm' => 'length'
        ],
        'single_blank_line_at_eof' => true, // Adds a single blank line at the end of each file
        'no_trailing_whitespace' => true,
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'one',
                'method' => 'one',
                'property' => 'one',
                'trait_import' => 'none',
                'case' => 'none'
            ],
        ],
        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'return', 'throw', 'try'],
        ],

        // Array Notation
        'array_syntax' => ['syntax' => 'short'],
        'no_multiline_whitespace_around_double_arrow' => true,
        'array_indentation' => true,  // Indentation within arrays
        'trailing_comma_in_multiline' => [
            'after_heredoc' => true,
            'elements' => ['arrays', 'array_destructuring'],
        ],

        // Imports
        'no_leading_import_slash' => true,
        'no_unneeded_import_alias' => true,
        'no_unused_imports' => true,
        'single_line_after_imports' => true,
        'single_import_per_statement' => false,
        'group_import' => true, // Group imports by namespace

        // Phpdoc
        'phpdoc_summary' => true, // Ensure single-line summary for each DocBlock
        'phpdoc_order' => true, // Orders @param, @return, @throws in DocBlocks
        'phpdoc_trim' => true, // Removes extra blank lines in DocBlocks
        'phpdoc_align' => ['align' => 'left'], // Aligns tags in DocBlocks vertically
        'phpdoc_no_empty_return' => true, // Removes empty @return tags in void functions
        'no_blank_lines_after_phpdoc' => true, // Prevents blank lines after a docblock

        'binary_operator_spaces' => [
            'operators' => ['=>' => 'single_space'],
        ],
        'cast_spaces' => ['space' => 'single'],
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => false
        ],
    ])
    ->setFinder($finder);
