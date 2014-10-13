<?php

namespace PPP\Wikidata;

use PPP\Module\ModuleEntryPoint;

require_once(__DIR__ . '/../vendor/autoload.php');


$entryPoint = new ModuleEntryPoint(new WikidataRequestHandler(
	'https://www.wikidata.org/w/api.php',
	'https://wdq.wmflabs.org/api'
));
$entryPoint->exec();
