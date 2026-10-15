<?php

// Mêmes règles que app/.php-cs-fixer.dist.php, appliquées au SDK (voir `make fixer`).
$finder = new PhpCsFixer\Finder()
    ->in([__DIR__ . '/src', __DIR__ . '/tests']);

return new PhpCsFixer\Config()
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache')
    ->setRules([
        '@PHP8x1Migration' => true,
        '@PER-CS' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'logical_operators' => false,
        'native_function_invocation' => false,
        'native_constant_invocation' => false,
        'is_null' => false,
        // Le SDK vise PHP 8.2 et garde des types stricts : on garde les @param/@return utiles à PHPStan
        'declare_strict_types' => true,
        'no_superfluous_phpdoc_tags' => false,
        'concat_space' => [
            'spacing' => 'one',
        ],
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => false,
        ],
    ])
    ->setFinder($finder);
