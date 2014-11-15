# PPP Wikidata

[![Build Status](https://scrutinizer-ci.com/g/ProjetPP/PPP-Wikidata/badges/build.png?b=master)](https://scrutinizer-ci.com/g/ProjetPP/PPP-Wikidata/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/ProjetPP/PPP-Wikidata/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ProjetPP/PPP-Wikidata/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ProjetPP/PPP-Wikidata/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ProjetPP/PPP-Wikidata/?branch=master)


PPP-Wikidata is a PPP module that use [Wikidata](http://www.wikidata.org) content.

## Installation

1 - Clone the repository:

    git clone https://github.com/ProjetPP/PPP-Wikidata.git

2 - Install dependances with composer:

    curl -sS https://getcomposer.org/installer | php
    php composer.phar install

3 - Make www/index.php executable by a web server, and put an URL to this
  web server in the configuration of your PPP core (and make sure the latter
  can access it)


## Custom Nodes

Wikidata module defines a new type of `resource` node, with `value-type` `wikibase-entity` that represents an entity in Wikidata.

Additional attributes:
* `entity-id` (required) the id of the entity as string like `Q42`.
* `description` (optional) a string description of the entity.

Example:                                                     
```
{
	"type": "resource",
	"value": "Douglas Adams",
	"value-type":"wikibase-entity",
	"entity-id":"Q42",
	"description":"Author"
}
```
