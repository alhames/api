<?php

/**
 * @see https://github.com/FriendsOfPHP/PHP-CS-Fixer
 */

$header = <<<EOF
This file is part of the Common API Interface package.

(c) Pavel Logachev <alhames@mail.ru>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

return \PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unreachable_default_argument_value' => false,
        'heredoc_to_nowdoc' => false,
        'combine_consecutive_unsets' => true,
        'dir_constant' => true,
        'ereg_to_preg' => true,
        'header_comment' => ['header' => $header],
        'no_short_echo_tag' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => true,
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_order' => true,
        'strict_comparison' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        \PhpCsFixer\Finder::create()
        ->files()
        ->name('*.php')
        ->in(__DIR__.'/src')
    );
