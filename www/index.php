<?php

namespace PPP\Wikidata;

use PPP\Module\ModuleEntryPoint;

require_once(__DIR__ . '/../vendor/autoload.php');

$configFile = getenv( 'PPP_WIKIDATA_CONFIG' );
$configFile = $configFile ?: __DIR__ . '/../default-config.json';

$entryPoint = new ModuleEntryPoint(new WikidataRequestHandler(
	$configFile,
	array(
		'arwiki' => 'http://ar.wikipedia.org/w/api.php',
		'dewiki' => 'http://de.wikipedia.org/w/api.php',
		'enwiki' => 'http://en.wikipedia.org/w/api.php',
		'eswiki' => 'http://es.wikipedia.org/w/api.php',
		'frwiki' => 'http://fr.wikipedia.org/w/api.php',
		'itwiki' => 'http://it.wikipedia.org/w/api.php',
		'jawiki' => 'http://ja.wikipedia.org/w/api.php',
		'nlwiki' => 'http://nl.wikipedia.org/w/api.php',
		'plwiki' => 'http://pl.wikipedia.org/w/api.php',
		'ptwiki' => 'http://pt.wikipedia.org/w/api.php',
		'ruwiki' => 'http://ru.wikipedia.org/w/api.php',
		'zhwiki' => 'http://zh.wikipedia.org/w/api.php'
	)
));
$entryPoint->exec();
