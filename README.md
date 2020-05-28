# Neo4j Http Adapter

[![Latest Version on Packagist][ico-version]][link-packagist]
![run-tests](https://github.com/JustSteveKing/neo4j-http-adapter/workflows/run-tests/badge.svg)
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

**This package is still a work in progress, what I have tested works so far but should not be used in a production environment yet**

The purpose of this package is to provide a clean and easy way to work with the neo4j HTTP API v4 to run Cypher queries and return data.


## Installation

Using composer:

```bash
$ composer require juststeveking/neo4j-http-adapter
```

You are then free to use it as needed within your projects.


## Usage

Using this library is relatively simple.


### Prepare your Adapter

The adapter build method accepts 2 parameters, connection string for your neo4j database and a database name that may be null.

Please note that an Exception will be thrown if your connection string does not start with either `http` or `https`

```php
<?php

use JustSteveKing\Graph\Connection\Adapters\Neo4j\Adapters\HttpAdapter;

$adapter = HttpAdapter::build(
    'http://neo4j:password@localhost:7474',
    'my_database'
);
```


### Selecting a database to query against

```php
<?php

use JustSteveKing\Graph\Connection\Adapters\Neo4j\Adapters\HttpAdapter;

$adapter = HttpAdapter::build(
    'http://neo4j:password@localhost:7474',
    'my_database'
);

$database = $adapter->on('database-name');
```


### Starting to build up a transaction

Once you have an active adapter, and have chosen your database, you will want to start running queries against it.

```php
<?php

use JustSteveKing\Graph\Connection\Adapters\Neo4j\Adapters\HttpAdapter;

$adapter = HttpAdapter::build(
    'http://neo4j:password@localhost:7474',
    'my_database'
);

$database = $adapter->on('database-name');

// Add a query to the transaction pipeline.
$database->query('MATCH (person:Person) WHERE person.name = "Tom Hanks" RETURN person');

// Add another query to the transaction pipeline
$database->query('MATCH (film:Film) WHERE film.name = "Forrest Gump" RETURN film');
```


### From here we are free to send this transaction to the server

```php
use JustSteveKing\Graph\Connection\Adapters\Neo4j\Adapters\HttpAdapter;

$adapter = HttpAdapter::build(
    'http://neo4j:password@localhost:7474',
    'my_database'
);

$database = $adapter->on('database-name');

// Add a query to the transaction pipeline.
$database->query('MATCH (person:Person) WHERE person.name = "Tom Hanks" RETURN person');

$response = $database->send();
```


### A cleaner approach

The point with this package was to allow you to build up your queries and send them as *you* see fit, not how I think they should be handled.
Here is an alternative method for sending queries:


```php
<?php

use JustSteveKing\Graph\Connection\Adapters\Neo4j\Adapters\HttpAdapter;

$adapter = HttpAdapter::build(
    'http://neo4j:password@localhost:7474',
    null
);

$actorsDatabase = $adapter->on('actors');

$tomHanks = $actorsDatabase->query('MATCH (person:Person) WHERE person.name = "Tom Hanks" RETURN person')->send();
```

Or if you would prefer to chain parts of the process:

```php
<?php

use JustSteveKing\Graph\Connection\Adapters\Neo4j\Adapters\HttpAdapter;

$tomHanks = HttpsAdapter::build(
    'http://neo4j:password@localhost:7474',
    'actors'
)->query('MATCH (person:Person) WHERE person.name = "Tom Hanks" RETURN person')->send();
```


If you want to use my other packages also, you can query like below:


```php
<?php

use JustSteveKing\Graph\Builder\Cypher;
use JustSteveKing\Graph\Connection\ConnectionManager;
use JustSteveKing\Graph\Connection\Adapters\Neo4j\Adapters\HttpAdapter;

$connection = ConnectionManager::create(HttpAdapter::build('http://neo4j:password@localhost:7474', null));
$query = Cypher::query()->match('Person', 'person')->where('person', 'name', '=', 'Tom Hanks')->return('person');
$connection->use('neo-http')->on('neo4j')->query($query)->send();
```

## Tests

There is a composer script available to run the tests:

```bash
$ composer run test
```

However, if you are unable to run this please use the following command:

```bash
$ ./vendor/bin/phpunit --testdox
```

## Security

If you discover any security related issues, please email juststevemcd@gmail.com instead of using the issue tracker.


[ico-version]: https://img.shields.io/packagist/v/juststeveking/neo4j-http-adapter.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/juststeveking/neo4j-http-adapter.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/JustSteveKing/neo4j-http-adapter.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/juststeveking/neo4j-http-adapter
[link-downloads]: https://packagist.org/packages/juststeveking/neo4j-http-adapter
[link-author]: https://github.com/JustSteveKing
[link-code-quality]: https://scrutinizer-ci.com/g/JustSteveKing/neo4j-http-adapter
