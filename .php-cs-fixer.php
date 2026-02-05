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
    ])
    ->setFinder($finder);
