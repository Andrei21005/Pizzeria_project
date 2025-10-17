<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__.'/app')
    ->in(__DIR__.'/routes')
    ->in(__DIR__.'/database');

return (new Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => ['default' => 'single_space'],
        'blank_line_after_namespace' => true,
        'no_unused_imports' => true,
    ])
    ->setFinder($finder);