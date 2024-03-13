<?php

$finder = (new PhpCsFixer\Finder())
    ->in(dirname(__DIR__) . '/../src/')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;