<?php

if(php_sapi_name() !== 'cli') {
	die('Not an entry point');
}

if(!is_readable(__DIR__ . '/../vendor/autoload.php')) {
	die('You need to install this package with Composer before you can run the tests');
}

$loader = require_once(__DIR__ . '/../vendor/autoload.php');
$loader->addClassMap(array(
	'PPP\Wikidata\TreeSimplifier\NodeSimplifierBaseTest' => __DIR__ . '/phpunit/TreeSimplifier/NodeSimplifierBaseTest.php',
	'PPP\Wikidata\ValueFormatters\JsonLd\JsonLdFormatterTestBase' => __DIR__ . '/phpunit/ValueFormatters/JsonLd/JsonLdFormatterTestBase.php'
));
