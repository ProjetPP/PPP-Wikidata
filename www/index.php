<?php

namespace PPP\Wikidata;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\MemcachedCache;
use Memcached;
use PPP\Module\ModuleEntryPoint;

require_once(__DIR__ . '/../vendor/autoload.php');

//TODO: configuration system?
$cache = new ArrayCache();

if(class_exists('Memcached')) {
	$memcached = new Memcached();
	if($memcached->addServer('localhost', 11211)) {
		$cache = new MemcachedCache();
		$cache->setMemcached($memcached);
	}
}

$entryPoint = new ModuleEntryPoint(new WikidataRequestHandler(
	'https://www.wikidata.org/w/api.php',
	'https://wdq.wmflabs.org/api',
	$cache
));
$entryPoint->exec();
