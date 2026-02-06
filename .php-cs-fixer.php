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
        '@PSR12' => true,
        'no_unused_imports' => true,
        'single_blank_line_at_eof' => true, // Adds a single blank line at the end of each file
        'no_trailing_whitespace' => true,
    ])
    ->setFinder($finder);
