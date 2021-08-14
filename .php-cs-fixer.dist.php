<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    die('This script supports command line usage only. Please check your command.');
}

$rules = [
    '@PHPUnit57Migration:risky' => true,
    '@PHPUnit60Migration:risky' => true,
    '@PHPUnit75Migration:risky' => true,

    'php_unit_construct' => true,
    'php_unit_dedicate_assert' => true,
    'php_unit_expectation' => true,
    'php_unit_fqcn_annotation' => true,
    'php_unit_method_casing' => true,
    'php_unit_mock' => true,
    'php_unit_no_expectation_annotation' => true,
    'php_unit_set_up_tear_down_visibility' => true,
    'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
    'phpdoc_order_by_value' => true,
];

$config = (new \PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules($rules);

$finder = \PhpCsFixer\Finder::create()
    ->in('Classes')->in('Tests');

return $config->setFinder($finder);
