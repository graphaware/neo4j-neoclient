# GraphAware Neo4j PHP Client

## An Enterprise Grade Client for Neo4j

[![Build Status](https://travis-ci.org/graphaware/neo4j-php-client.svg?branch=master)](https://travis-ci.org/graphaware/neo4j-php-client)
[![Latest Stable Version](https://poser.pugx.org/graphaware/neo4j-php-client/v/stable.svg)](https://packagist.org/packages/graphaware/neo4j-php-client)
[![Total Downloads](https://poser.pugx.org/graphaware/neo4j-php-client/downloads.svg)](https://packagist.org/packages/graphaware/neo4j-php-client)
[![License](https://poser.pugx.org/graphaware/neo4j-php-client/license.svg)](https://packagist.org/packages/graphaware/neo4j-php-client)

## Introduction

Neo4j-PHP-Client is the most advanced and flexible [Neo4j](http://neo4j.com) Client for PHP.

### What is Neo4j?

Neo4j is a transactional, open-source graph database. A graph database manages data in a connected data structure, capable of representing any kind of data in a very accessible way. Information is stored in nodes and relationships connecting them, both of which can have arbitrary properties. To learn more visit [What is a Graph Database](http://neo4j.com/developer/graph-database/)?

### Key features

* Supports multiple connections
* Support for Bolt binary protocol
* Built-in and automatic support for *Neo4j Enterprise HA Master-Slave Mode* with auto slaves fallback

#### Neo4j Version Support

| **Version** | **Tested**  |
|-------------|-------------|
| <= 2.2.6    |   No        |
| >= 2.2.6    |   Yes       |
| 2.2         |   Yes       |
| 2.3         |   Yes       |
| 3.0 +       |   Yes       |

#### Neo4j Feature Support

| **Feature**          | **Supported?** |
|----------------------|----------------|
| Auth                 |  Yes           |
| Remote Cypher        |  Yes           |
| Transactions         |  Yes           |
| High Availability    |  Yes           |
| Embedded JVM support |  No            |
| Binary Protocol      |  Yes           |

### Requirements

* PHP >= 5.6
* ext-bcmath
* ext-mbstring
* A Neo4j database (minimum version 2.2.6)

### Getting Help

You can:

 * [Ask a question on StackOverflow](http://stackoverflow.com/questions/ask?tags=graphaware,php,neo4j)
 * For bugs, please feel free to create a [new issue on GitHub](https://github.com/graphaware/neo4j-php-client/issues/new)

### Implementations

* [Symfony Framework Bundle](https://github.com/neo4j-contrib/neo4j-symfony)

## Installation and basic usage

### Installation

Add the library to your composer dependencies :

```bash
composer require graphaware/neo4j-php-client:^4.0
```

Require the composer autoloader, configure your connection by providing a connection alias and your connection settings :

```php
<?php

require_once 'vendor/autoload.php';

use GraphAware\Neo4j\Client\ClientBuilder;

$client = ClientBuilder::create()
    ->addConnection('default', 'http://neo4j:password@localhost:7474') // Example for HTTP connection configuration (port is optional)
    ->addConnection('bolt', 'bolt://neo4j:password@localhost:7687') // Example for BOLT connection configuration (port is optional)
    ->build();
```

You're now ready to connect to your database.

NB: The build method will process configuration settings and return you a `Client` instance.

### Basic Usage

#### Sending a Cypher Query

```php
$client->run('CREATE (n:Person)');
```

#### Sending a Cypher Query with parameters

```php
$client->run('CREATE (n:Person) SET n += {infos}', ['infos' => ['name' => 'Ales', 'age' => 34]]);
```

#### Reading a Result

```php
$result = $client->run('MATCH (n:Person) RETURN n');
// a result always contains a collection (array) of Record objects

// get all records
$records = $result->getRecords();

// get the first or (if expected only one) the only record

$record = $result->getRecord();
```

A `Record` object contains the values of one record from your Cypher query :

```php
$query = 'MATCH (n:Person)-[:FOLLOWS]->(friend) RETURN n.name, collect(friend) as friends';
$result = $client->run($query);

foreach ($result->getRecords() as $record) {
    echo sprintf('Person name is : %s and has %d number of friends', $record->value('name'), count($record->value('friends')));
}
```

### Cypher statements and Stacks

Ideally, you would stack your statements and issue them all at once in order to improve performance.

You can create Cypher statement stacks that act as a Bag and run this stack with the client, example :

```php
$stack = $client->stack();

$stack->push('CREATE (n:Person {uuid: {uuid} })', ['uuid' => '123-fff']);
$stack->push('MATCH (n:Person {uuid: {uuid1} }), (n2:Person {uuid: {uuid2} }) MERGE (n)-[:FOLLOWS]->(n2)', ['uuid1' => '123-fff', 'uuid2' => '456-ddd']);

$results = $client->runStack($stack);
```

### Tagging your Cypher statements

Sometimes, you may want to retrieve a specific result from a Stack, an easy way to do this is to tag your Cypher statements.

The tag is passed via the 3rd argument of the `run` or `push` methods :

```php
$stack = $client->stack();

$stack->push('CREATE (n:Person {uuid: {uuid} })', ['uuid' => '123-fff'], 'user_create');
$stack->push('MATCH (n:Person {uuid: {uuid1} }), (n2:Person {uuid: {uuid2} }) MERGE (n)-[r:FOLLOWS]->(n2) RETURN id(r) as relId', ['uuid1' => '123-fff', 'uuid2' => '456-ddd'], 'user_follows');

$results = $client->runStack($stack);

$followResult = $results->get('user_follows');
$followRelationshipId = $followResult->getRecord()->value('relId');
```

### Working with Result sets


#### Basics

The `run` method returns you a single `Result` object. Other methods where you can expect multiple results returns a `ResultCollection` object which is Traversable.

The `Result` object contains the `records` and the `summary` of the statement, the following methods are available in the API :

```php

$result->firstRecord(); // Returns the first record of the Statement Result

$result->records(); // Returns all records

$result->summarize(); // Returns the ResultSummary
```

#### Summary

The `ResultSummary` contains the `Statement`, the Statistics and the QueryPlan if available :

```php
$summary = $result->summarize();

$query = $summary->satement()->text();

$stats = $summary->updateStatistics();

$nodesUpdated = $stats->nodesUpdated();
$propertiesSet = $stats->propertiesSet();

// Does the statement affected the graph ?
$affected = $stats->containsUpdates();
```

#### Record Values

Each record contains one row of values returned by the Cypher query :

```

$query = 'MATCH (n:Person) n, n.name as name, n.age as age';
$result = $client->run($query);

foreach ($result->records() as $record) {
    print_r($record->get('n')); // nodes returned are automatically hydrated to Node objects

    echo $record->value('name') . PHP_EOL;
    echo $record->value('age') . PHP_EOL;
}
```

The client takes care of the hydration of Graph objects to PHP Objects, so it is for Node, Relationship and Path :

##### Node

* `labels()` : returns an array of labels (string)
* `identity()` : returns the internal ID of the node
* `values()` : returns the properties of the node (array)
* `value($key)` : returns the value for the given property key
* `hasValue($key)` : returns whether or not the nodes has a property with the given key
* `keys()` : returns you an array representing the keys of the node properties
* `hasLabel($label)` : returns whether or not the node has the given label (boolean)


##### Relationship

* `type()` : returns the relationship type
* `identity()` : returns the internal ID of the relationship
* `values()` : returns the properties of the relationship (array)
* `value($key)` : returns the value for the given property key
* `hasValue($key)` : returns whether or not the relationship has a property with the given key
* `keys()` : returns you an array representing the keys of the relationship properties
* `startNodeIdentity` : returns the start node id
* `endNodeIdentity` : returns the end node id

##### Path

* `start()` : returns the start node of the path
* `end()` : returns the end node of the path
* `length()` : returns the length of the path
* `nodes()` : returns all the nodes in the path
* `relationships` : returns all the relationships in the path

#### Handling Results (from v3 to v4)


There are 3 main concepts around this topic :

1. a Result
2. a Record
3. a RecordValue

Let's take a look at a query we do in the browser containing multiple possibilities of types :

```
MATCH (n:Address)
RETURN n.address as addr, n, collect(id(n)) as ids
LIMIT 5
```

![screen shot 2016-05-11 at 20 54 34bis](https://cloud.githubusercontent.com/assets/1222009/15192806/1a39cb30-17bb-11e6-8687-ed861411af2d.png)


##### Result

A `Result` is a collection of `Record` objects, every **row** you see in the browser is a `Record` and contains `Record Value`s.

* In blue the Result
* In orange a Record
* In green a RecordValue

##### Record

In contrary to the previous versions of the client, there is no more automatic merging of all the records into one big record, so you will need to iterate all the records from the `Result` :

```php
$query = 'MATCH (n:Address)
RETURN n.address as addr, n, collect(id(n)) as ids
LIMIT 5';
$result = $client->run($query);

foreach ($result->records() as $record) {
  // here we do what we want with one record (one row in the browser result)
  print_r($record);
}
```

##### Record Value

Every record contains a collection of `Record Value`s, which are identified by a `key`, the key is the identifier you give in the `RETURN` clause of the Cypher query. In our example, a `Record` will contain the following keys :

* addr
* n
* ids

In order to access the value, you make use of the `get()` method on the `Record` object :

```php
$address = $record->get('addr');
```

The type of the value is depending of what you return from Neo4j, in our case the following values will be returned :

* a `string` for the `addr` value
* a `Node` for the `n` value
* an `array` of `integers` for the `ids` value

Meaning that :

```php
$record->get('addr'); // returns a string
$record->get('n'); // returns a Node object
$record->get('ids'); // returns an array
```

`Node`, `Relationship` and `Path` objects have then further methods, so if you know that the node returned by the identifier `n` has a `countries` property on it, you can access it like this :

```php
$addressNode = $record->get('n');
$countries = $addressNode->value('countries');
```

The `Record` object contains three methods for IDE friendlyness, namely :

```php
$record->nodeValue('n');
$record->relationshipValue('r');
$record->pathValue('p');
```

This does not offer something extra, just that the docblocks hint the IDE for autocompletion.


##### Extra: ResultCollection

When you use `Stack` objects for sending multiple statements at once, it will return you a `ResultCollection` object containing a collection of `Result`s. So you need to iterate the results before accessing the records.


### Working with Transactions

The Client provides a Transaction object that ease how you would work with transactions.

#### Creating a Transaction

```php
$tx = $client->transaction();
```

At this stage, nothing has been sent to the server yet (the statement BEGIN has not been sent), this permits to stack queries or Stack objects before commiting them.

#### Stack a query

```
$tx->push('CREATE (n:Person) RETURN id(n)');
```

Again, until now nothing has been sent.

#### Run a query in a Transaction

Sometimes you want to get an immediate result of a statement inside the transaction, this can be done with the `run` method :

```php
$result = $tx->run('CREATE (n:Person) SET n.name = {name} RETURN id(n)', ['name' => 'Michal']);

echo $result->getRecord()->value("id(n)");
```

If the transaction has not yet begun, the BEGIN of the transaction will be done automatically.

#### You can also push or run Stacks

```php
$stack = $client->stack();
$stack->push('CREATE (n:Person {uuid: {uuid} })', ['uuid' => '123-fff']);
$stack->push('MATCH (n:Person {uuid: {uuid1} }), (n2:Person {uuid: {uuid2} }) MERGE (n)-[:FOLLOWS]->(n2)', ['uuid1' => '123-fff', 'uuid2' => '456-ddd']);

$tx->pushStack($stack);
// or
$results = $tx->runStack($stack);
```

### Commit and Rollback

if you have queued statements in your transaction (those added with the `push` methods) and you have finish your job, you can commit the transaction and receive
the results :

```php
$stack = $client->stack();
$stack->push('CREATE (n:Person {uuid: {uuid} })', ['uuid' => '123-fff']);
$stack->push('MATCH (n:Person {uuid: {uuid1} }), (n2:Person {uuid: {uuid2} }) MERGE (n)-[:FOLLOWS]->(n2)', ['uuid1' => '123-fff', 'uuid2' => '456-ddd']);

$tx->pushStack($stack);
$tx->pushQuery('MATCH (n) RETURN count(n)');

$results = $tx->commit();
```

After a commit, you will not be able to `push` or `run` statements in this transaction.

### Working with multiple connections

Generally speaking, you would better use HAProxy for running Neo4j in a cluster environment. However sometimes it makes sense to
have full control to which instance you send your statements.

Let's assume a environment with 3 neo4j nodes :

```php
$client = ClientBuilder::create()
    ->addConnection('node1', 'bolt://10.0.0.1')
    ->addConnection('node2', 'bolt://10.0.0.2')
    ->addConnection('node3', 'bolt://10.0.0.3')
    ->setMaster('node1')
    ->build();
```

By default, the `$client->run()` command will send your Cypher statements to the first registered connection in the list.

You can specify to which connection to send the statement by specifying its alias as 4th argument to the run parameter :

```php
$result = $client->run('CREATE (n) RETURN n', null, null, 'node1');
```

The client is also aware of the manually configured master connection, so sending your writes can be easier with :

```php
$client->runWrite('CREATE (n:User {login: 123})');
```

### Helper Methods

```
$client->getLabels();
```

Returns an array of `Label` objects.

### Event Dispatching

3 types of events are dispatched during the `run` methods :

* `PreRunEvent` : before the statement or stack is run.
* `PostRunEvent` : after the statement or stack is run.
* `FailureEvent` : in case of failure, you can disable the client to throw an exception with this event.

##### Registering listeners

Example :

```php
$client = ClientBuilder::create()
    ->addConnection('default', 'bolt://localhost')
    ->registerEventListener(Neo4jClientEvents::NEO4J_PRE_RUN, array($listener, 'onPreRun')
    ->build();
```

The event dispatcher is available via the client with the `$client->getEventDispatcher` methods.

### Settings

#### Timeout (deprecated)

You can configure a global timeout for the connections :

```php
$client = ClientBuilder::create()
    ->addConnection('default', 'http://localhost:7474')
    ->setDefaultTimeout(3)
    ->build();
```

The timeout by default is 5 seconds.

This feature is deprecated and will be removed in version 5. See Http client settings below. 

### TLS

You can enable TLS encryption for the Bolt Protocol by passing a `Configuration` instance when building the connection, here
is a simple example :

```
$config = \GraphAware\Bolt\Configuration::newInstance()
    ->withCredentials('bolttest', 'L7n7SfTSj')
    ->withTLSMode(\GraphAware\Bolt\Configuration::TLSMODE_REQUIRED);

$client = ClientBuilder::create()
    ->addConnection('default', 'bolt://hodccomjfkgdenl.dbs.gdb.com:24786', config)
    ->build();
```

#### HTTP client settings

We use HTTPlug to give you full control of the HTTP client. Version 4 of the Neo4jClient comes with Guzzle6 by default
to preserve backward compatibility. Version 5 will give you the option to choose whatever client you want. Read more
about HTTPlug [in their documentation](http://docs.php-http.org/en/latest/httplug/users.html).

To configure your client you may add it to `Configuration`. 

```php
$options = [
    CURLOPT_CONNECTTIMEOUT => 3, // The number of seconds to wait while trying to connect.
    CURLOPT_SSL_VERIFYPEER => false // Stop cURL from verifying the peer's certificate
];
$httpClient = new Client(null, null, $options);

$config = \GraphAware\Neo4j\Client\HttpDriver\Configuration::create($httpClient);
$client = ClientBuilder::create()
    ->addConnection('default', 'http://neo4j:password@localhost:7474', config)
    ->build();
```

### License

The library is released under the MIT License, refer to the LICENSE file bundled with this package.
