<?php

namespace PPP\Wikidata;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\MemcachedCache;
use Memcached;
use PPP\Module\ModuleEntryPoint;

require_once(__DIR__ . '/../vendor/autoload.php');

//TODO: configuration system?
$cache = new ArrayCache();

if(class_exists('Memcached')) {
	$memcached = new Memcached();
	if($memcached->addServer('localhost', 11211)) {
		$memcachedCache = new MemcachedCache();
		$memcachedCache->setMemcached($memcached);
		$cache = new ChainCache(array($cache, $memcachedCache));
	}
}

$entryPoint = new ModuleEntryPoint(new WikidataRequestHandler(
	'https://www.wikidata.org/w/api.php',
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
	),
	'https://wdq.wmflabs.org/api',
	$cache
));
$entryPoint->exec();
