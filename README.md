# PPP Wikidata

[![Build Status](https://scrutinizer-ci.com/g/ProjetPP/PPP-Wikidata/badges/build.png?b=master)](https://scrutinizer-ci.com/g/ProjetPP/PPP-Wikidata/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/ProjetPP/PPP-Wikidata/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ProjetPP/PPP-Wikidata/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ProjetPP/PPP-Wikidata/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ProjetPP/PPP-Wikidata/?branch=master)


PPP-Wikidata is a PPP module that use [Wikidata](http://www.wikidata.org) content.

## Installation

1 - Clone the repository:
  git clone git@github.com:ProjetPP/PPP-Wikidata.git

2 - Install dependances with composer:
  curl -sS https://getcomposer.org/installer | php
  php composer.phar install

3 - Make www/index.php executable by a web server, and put an URL to this
  web server in the configuration of your PPP core (and make sure the latter
  can access it)

