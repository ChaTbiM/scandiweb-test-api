<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/seeds')
    ->in(__DIR__ . '/migrations');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR1'                => true,
        '@PSR12'               => true,
        'declare_strict_types' => true,
    ])
    ->setFinder($finder);
