<?php

/*
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$header = <<<'EOF'
This file is part of the GraphAware Neo4j Client package.

(c) GraphAware Limited <http://graphaware.com>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;
!Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader($header);

$finder = (new \Symfony\Component\Finder\Finder())
    ->files()
    ->ignoreVCS(true)
    ->name('*.php')
    ->in(__DIR__.'/src/')
    ->in(__DIR__.'/tests/')
;

return Symfony\CS\Config\Config::create()
    ->setUsingCache(true)
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->fixers([
        'ereg_to_preg', // Replace deprecated ereg regular expression functions with preg.
        'header_comment',
        'no_useless_return', // There should not be an empty return statement at the end of a function.
        'newline_after_open_tag', // Ensure there is no code on the same line as the PHP open tag.
        'ordered_use', // Ordering use statements.
        'php4_constructor', // Convert PHP4-style constructors to __construct. Warning! This could change code behavior.
        'phpdoc_order', // Annotations in phpdocs should be ordered so that param annotations come first, then throws annotations, then return annotations.
        'short_array_syntax', // PHP arrays should use the PHP 5.4 short-syntax.
        'strict', // Comparison should be strict.
        'strict_param',
    ])
    ->finder($finder)
    ->setUsingCache(true)
;
