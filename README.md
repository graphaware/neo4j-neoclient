# Neo4j-PHP-Client

## An Enterprise Grade Client for Neo4j

[![Build Status](https://travis-ci.org/graphaware/neo4j-php-client.svg?branch=master)](https://travis-ci.org/graphaware/neo4j-php-client)
[![Latest Stable Version](https://poser.pugx.org/graphaware/neo4j-php-client/v/stable.svg)](https://packagist.org/packages/graphaware/neo4j-php-client)
[![Total Downloads](https://poser.pugx.org/neoxygen/neoclient/downloads.svg)](https://packagist.org/packages/neoxygen/neoclient)
[![Latest Unstable Version](https://poser.pugx.org/neoxygen/neoclient/v/unstable.svg)](https://packagist.org/packages/neoxygen/neoclient)
[![License](https://poser.pugx.org/neoxygen/neoclient/license.svg)](https://packagist.org/packages/neoxygen/neoclient)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/bac840f0-7b30-4206-a0e0-c6f4ca320077/big.png)](https://insight.sensiolabs.com/projects/bac840f0-7b30-4206-a0e0-c6f4ca320077)


## Introduction

Neo4j-PHP-Client is the most advanced and flexible [Neo4j](http://neo4j.com) Client for PHP. 

### What is Neo4j?

Neo4j is a transactional, open-source graph database. A graph database manages data in a connected data structure, capable of representing any kind of data in a very accessible way. Information is stored in nodes and relationships connecting them, both of which can have arbitrary properties. To learn more visit [What is a Graph Database](http://neo4j.com/developer/graph-database/)?

### Key features

* Supports multiple connections
* Built-in and automatic support for *Neo4j Enterprise HA Master-Slave Mode* with auto slaves fallback
* Fully extensible (You can create your own extensions)

#### Neo4j Version Support

| **Version** | **Tested**  |
|-------------|-------------|
| <= 2.1.5    |   No        |
| >= 2.1.6    |   Yes       |
| 2.2         |   Yes       |
| 2.3         |   Yes       |

#### Neo4j Feature Support

| **Feature**          | **Supported?** |
|----------------------|----------------|
| Auth                 |  Yes         |
| Remote Cypher        |  Yes         |
| Transactions         |  Yes         |
| High Availability    |  Yes         |
| Embedded JVM support |  No          |
| Binary Protocol      |  In progress |

### Requirements

* PHP >= 5.5, < 8.0
* A Neo4j database (minimum version 2.1.6)

### Getting Help

You can:

 * Check out an [example application build with NeoClient](https://github.com/neo4j-contrib/developer-resources/tree/gh-pages/language-guides/php/neoclient)
 * [Ask a question on StackOverflow](http://stackoverflow.com/questions/ask?tags=neo4j-neoclient-php,php,neo4j)
 * Chat with us on Gitter: [![Gitter](https://badges.gitter.im/Join Chat.svg)](https://gitter.im/neoxygen/neo4j-neoclient?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
 * For bugs, please feel free to create a [new issue on GitHub](https://github.com/neoxygen/neo4j-neoclient/issues/new)
 
## Installation and basic usage

### Installation

Add the library to your composer dependencies :

```bash
composer require graphaware/neo4j-php-client
```

Require the composer autoloader, configure your connection by providing a connection alias and your connection settings :

```php
<?php

require_once 'vendor/autoload.php';

use Neoxygen\NeoClient\ClientBuilder;

$client = ClientBuilder::create()
    ->addConnection('default','http','localhost',7474)
    ->build();
```

You're now ready to connect to your database.

If you use default database settings in a local environment (meaning _http://localhost:7474_), you can use the handy `addDefaultLocalConnection` method :

```php
$client = ClientBuilder::create()
    ->addDefaultLocalConnection()
    ->build();
```

The build method will process configuration settings and return you a `Client` instance.

#### Configuring the connection timeout

You can configure the default timeout during the build process :

```php
$client = ClientBuilder::create()
    ->addDefaultLocalConnection()
    ->setDefaultTimeout(20) // <-- Timeout of 20 seconds for http requests
    ->build();
```

### Usage

You have now full access to the database.


#### getRoot | Returns the root endpoint

```php
$root = $client->getRoot();
```

```php
Array
    (
        [management] => http://localhost:7474/db/manage/
        [data] => http://localhost:7474/db/data/
    )
```

Note: As the library provide full support for working with multiple databases, each method explained in the documentation can take 
a `$conn` parameter which you can use to define on which connection you want to execute the method. The default connection will be used when
the parameter is not set.

For more information on how to set up multiple connections, read the `Multiple connections` section of the documentation.


#### getNeo4jVersion | Returns the Neo4j version of the current connection

```php
$version = $client->getNeo4jVersion();

// Returns (string) 2.2.1
```

## Sending Cypher Queries

In order to send a Cypher Query, you need to pass the query as a string, and an optional array of paramaters as arguments :

```php

$q = 'MATCH (n) RETURN count(n)';
$response = $client->sendCypherQuery($q);
```

````php
Array
(
    [results] => Array
        (
            [0] => Array
                (
                    [columns] => Array
                        (
                            [0] => count(n)
                        )

                    [data] => Array
                        (
                            [0] => Array
                                (
                                    [row] => Array
                                        (
                                            [0] => 1
                                        )
......                                        
```

Handling such response format is not really practical and boring. You can ask the client to format the response in a pretty way and have
this format available to you :

```php
$client = ClientBuilder::create()
    ->addDefaultLocalConnection()
    ->setAutoFormatResponse(true)
    ->build();
```

To get the pretty format :

```php

$q = 'MATCH (n:Actor) RETURN n.name';
$client->sendCypherQuery($q);

$result = $client->getRows();
```

```
Array
(
    [n.name] => Array
        (
            [0] => Keanu Reeves
            [1] => Laurence Fishburne
            [2] => Carrie-Anne Moss
        )

)

```
## Labels, Indexes and Constraints Management

### Managing labels

The library provide handy methods for managing your labels :


#### getLabels | Returns the labels indexed in the database

```php
$labels = $client->getLabels();
```

```php
[ "UriahHeep", "MyLabel", "Version", "Customer", "TestLabel" ]
```

#### renameLabel | Fetch all nodes for that label and rename the label of the nodes

Note that depending on the amount of nodes for the given label, this can take some time.

Call the `renameLabel` method and pass the old label name and the new label name as arguments :

```php
$client->renameLabel('Person', 'User');
```

### Managing Indexes and Constraints

Indexes and Constraints management is also an easy task

#### createIndex | Creates an index for a label/property pair

```php
$client->createIndex('Person', 'email');
```

#### listIndex | List indexed properties for a given label

```php
$client->listIndex('Person');
```

Returns you an array of indexed properties for the given label

#### listIndexes | List indexed properties for given labels or all labels

```php
$personAndUserIndexes = $client->listIndexes(['Person','User']);

$allIndexes = $client->listIndexes();
```

Returns you an array of indexed properties by the form `['Label' => ['prop1','prop2']]`.

#### dropIndex | Drop an index for a given label/property pair

```php
$client->dropIndex('Person','email');
```

#### isIndexed | Checks whether or not a given label/property pair is indexed

```php
$client->isIndexed('User','username');
```

Returns true or false

#### createUniqueConstraint | Create a uniqueness constraint for a given label/property pair

```php
$client->createUniqueConstraint('User','username');
```

If an index already exist on the combination `Label, property` you can ask the client to drop the index and create the
constraint instead of throwing an exception, just pass `true` as a third parameter.

```php
$client->createUniqueConstraint('User','username',true);
```

#### dropUniqueConstraint | Drop a uniqueness constraint for a given label/property pair

```php
$client->dropUniqueConstraint('User','username');
```

#### getUniqueConstraints | Returns all the uniqueness constraints by label

```php
$constraints = $client->getUniqueConstraints();
```

Returns `['User' => ['username','email'], 'Movie' => ['imdb_id']]`


## Handling Graph Results

The Response Formatter will format graph results in a pretty format of nodes and relationships objects.

If you've setup the `autoFormatResponse` configuration value, when a graph result is available, a graph representation
is available for you :

```php
$query = 'MATCH (a:Actor)-[r]-(m:Movie) RETURN *';
$client->sendCypherQuery($query);

// Getting the graph Result
$result = $client->getResult();

// The raw response is still available :
$response = $client->getResponse();

// Getting all nodes

$nodes = $result->getNodes();

// Getting all movie nodes from the result
$movies = $result->getNodes('Movie');

// Getting all movie and Actor nodes from the result

$moviesAndActors = $result->getNodes(['Movie','Actor']);
// Returns you a collection of nodes objects

// If you want to group the nodes by labels, you can pass true as second argument to the getNodes method

$moviesAndActors = $result->getNodes(['Movie','Actor'], true);
// Returns an array with labels as keys ['Movie' => ['NodeObject1', 'NodeObject2']]


// Getting only one movie (returns in fact the first element of an array, but is handy when you expect only one node
$movie = $result->getSingleNode('Movie');

// Working with the relationships

$movie = $result->getSingleNode('Movie');
$actors = $movie->getRelationships('ACTS_IN');
// Or you may want to specify direction
$actors = $movie->getRelationships('ACTS_IN', 'IN');

// If you need only one relationship :
$actor = $movie->getSingleRelationship('ACTS_IN');

// Getting node/relationships properties

// Getting one property
$actor = $result->getSingleNode('Actor');
$name = $actor->getProperty('name');

// Getting all properties
$props = $actor->getProperties();

// Getting a set of properties
$props = $actor->getProperties(['name', 'date_of_birh']);

// Getting the node internal Id (Id of the Neo4j database)

$id = $actor->getId();

// Getting a node by id in the Result set

$node = $result->getNodeById(34);

// Counting Nodes And Relationships

$nbNodes = $result->getNodesCount();
$nbRels = $result->getRelationshipsCount();


// Since 2.2
// getConnectedNodes and getConnectedNode
// Shortcut bypassing the relationship and returning the connected nodes

$node->getConnectedNodes();
$node->getConnectedNodes('IN', 'KNOWS');
$node->getconnectedNodes('OUT', ['KNOWS','FOLLOWS']);
//Same arguments signature for getConnectedNode
$node->getConnectedNode(); // returns only one node

```

### Using `get`

Commonly, you'll use identifiers in your return statements, you can access them in an easy way :

```php
$q = 'MATCH (n:User)<-[:FOLLOWS]-(followers) RETURN n, collect(followers) as flwers';
$r = $client->sendCypherQuery($q)->getResult();

print_r($r->get('flwers')); // Returns an array of node objects
```

### Results in table format

Sometimes you will deal with results in table format, there is a dedicated method `getTableFormat` 
that will format the results for you :

```
$q = 'MATCH (c:Country)
      MATCH (c)<-[:LIVES_IN]->(p)
      RETURN c.name, count(*) as people
      ORDER BY people DESC';
$result = $client->sendCypherQuery($q)->getResult();

print_r($result->getTableFormat());

--- 
Array
(
    [0] => Array
        (
            [c.name] => Barbados
            [people] => 3
        )

    [1] => Array
        (
            [c.name] => Vietnam
            [people] => 2
        )

    [2] => Array
        (
            [c.name] => Liberia
            [people] => 2
        )

    [3] => Array
        (
            [c.name] => Rwanda
            [people] => 2
        )

    [4] => Array
        (
            [c.name] => Canada
            [people] => 1
        )
)
---
```


## Sending multiple statements in one transaction

There are 2 ways for sending multiple statements in one and only transaction.

1. Using an open transaction throughout the process (see the next section "Transaction Management")
2. Using a `PreparedTransaction` instance


### PreparedTransaction

Handy if you want to keep a `PreparedTransaction` instance throughout your code :

```php
$tx = $client->prepareTransaction()
    ->pushQuery($q, $p)
    ->pushQuery($q2)
    ->pushQuery($q3)
    ->commit();
```


## Transaction Management

The library comes with a Transaction Manager removing you the burden of parsing commit urls and transaction ids.

Usage is straightforward :

```php
$transaction = $client->createTransaction();
$transaction->pushQuery('MERGE (n:User {id: 123}) RETURN n');
$transaction->pushQuery('MATCH (n) RETURN count(n)');
$transaction->commit();

// Other methods :
$transaction->rollback();
$transaction->getLastResult // Returns the result of the last transaction statements
$transaction->getResults() // Returns the results of all the statements
```

Note that a commited or a rolled back transaction will not accept pushQuery calls anymore.

## Working with multiple connections

### Define multiple connections

You can work with as many connections you want :

```php
$client = ClientBuilder::create()
    ->addConnection('default', 'http', 'localhost', 7474)
    ->addConnection('testserver1', 'http', 'testserver.local', 7474)
    ->addConnection('testserver2', 'http', 'testserver2.local',7474)
    ->build();
```

When calling commands, you can specify to which connection the command has to be executed by passing the connection alias as argument :

```php
$client->getRoot('default');
$client->sendCypherQuery('MATCH (n) RETURN count(n) as total', array(), 'testserver1');
```

## HA (High-Availibilty)

### HA Mode for Neo4j Enterprise

NB: There are ongoing changes for improving the HA Mode of the Enterprise Edition, stay tuned ;-)

The library provide a powerful system for handling the HA Mode of Neo4j available in Neo4j Enterprise.

The convention is to send write queries to the master, and read queries to slaves.

To enable the HA Mode and defining which connections are master or slave, you need to add some method call during the build process of the
client :

```php

$client = ClientBuilder::create()
    ->addConnection('server1', 'http', '193.147.213.3', 7474)
    ->addConnection('server2', 'http', '193.147.213.4', 7474)
    ->addConnection('server3', 'http', '193.147.213.7', 7474)
    ->setMasterConnection('server1') // Define the Master Connection by providing the connection alias
    ->setSlaveConnection('server2') // Idem for slave connections
    ->setSlaveConnection('server3')
    ->enableHAMode()
    ->build();
```

Your configuration is now set. The client has convenience methods for HA usage, respectively `sendReadQuery` and `sendWriteQuery`.

Automatically, write queries will be executed against the `master` connection, while `read` queries against slave connections.

If a slave is no more reachable, it will automatically check if other slaves are configured. If yes it will attempt to send the query again 
to the other slave connections.

If you have loggers settled up, an `alert` entry will be logged to inform you of slave connection failure.

```php

$client->sendWriteQuery('MERGE (n:User {firstname: "Chris"})'); // Will be sent to the "server1" connection

$client->sendReadQuery('MATCH (n:User) RETURN n'); // Will be sent to the "server2" connection
```

NB: The above methods do not take the `$conn` argument as the choice of the connection is done in the library internals.

Note: You can always retrieve the Master and the first Slave connection alias from the client if you want to specify them when using other commands :

```php

$client->getRoot($client->getWriteConnectionAlias()); // Will be run against the master connection

$client->listLabels($client->getReadConnectionAlias()); // Will be run agains the first found slave connection
```

Please also note, that when using the *Transaction Manager*, all queries will be run against the same connection. *Transaction*  instances 
are bounded to one and only connection.

### Checking your Master/Slave Configuration

You can check that your defined master and slaves connections are running and setup correctly :

```php
$client->checkHAMaster('server1');      // Returns true|false
$client->checkHASlave('server2');       // Returns true|false
$client->checkHAAvailable('serverxxx'); // Returns master|slave|false
```


### Query Mode Headers

When the High Availibity Mode is enabled, an additional header will be set to the http request. This header defines the query mode of 
the transaction : `READ` or `WRITE`.

By default, all queries, live transactions and prepared transactions are assumed `WRITE`.

You can define it your self by using the Client's constants `Client::NEOCLIENT_QUERY_MODE_WRITE` and `Client::NEOCLIENT_QUERY_MODE_READ` 
or by simply passing a string with those values to the following methods:

```php
$client->sendCypherQuery($query, $params, $conn = null, $queryMode = Client::NEOCLIENT_QUERY_MODE_READ);

$client->createTransaction($conn = null, Client::NEOCLIENT_QUERY_MODE_WRITE);

$client->prepareTransaction($conn = null, Client::NEOCLIENT_QUERY_MODE_WRITE);
```

The default headers are the following :

* The header key is `Neo4j-Query-Mode`
* The write transactions will have a header value of : `NEO4J_QUERY_WRITE`
* The read transactions will have a header value of : `NEO4J_QUERY_READ`

You can define your own headers definition via the configuration :

##### yaml

```yaml
neoclient:
	ha_mode:
		enabled: true
		query_mode_header_key: MY_HEADER
		read_mode_header_value: QUERY_READ
		write_mode_header_value: QUERY_WRITE
```

##### php

```php
$client = ClientBuilder::create()
	// .. other settings
	->enableHAMode()
	->configureHAQueryModeHeaders($headerKey, $writeModeHeaderValue, $readModeHeaderValue)
	->build();
```

## Secured connections

### Authenticated connection

#### For Neo4j 2.2

Provide the user and the password when building the connection :

```php
$client = ClientBuilder::create()
    ->addConnection('default', 'http', 'myserver.dev', 7474, true, 'username', 'password')
    ->build();
```

#### changing the password

The client has a built-in method for changing the password :

```php
$client->changePassword('user', 'newPassword');
```

#### Before Neo4j 2.2 using the Auth Extension

If you are using the `authenticated-extension` or using [GrapheneDB](http://graphenedb.com) instance, you can specify to use the authMode for the connection and provide your username
and password :

```php
$client = ClientBuilder::create()
    ->addConnection('default', 'http', 'myserver.dev', 7474, true, 'username', 'password')
    ->build();
```

Your password will automatically be encoded in base64 for the Authorization.

### Convenience methods for the `Authentication extension`

#### listUsers | List the users registered in the connection authentication extension

```php
$client->listUsers();
```

```json
{"john"}
```

#### addUser | Adds a user to the extensions

```php
$client->addUser('John', 'password');
```

```json
OK
```

The third argument of the `addUser` method is the `readOnly` parameter, default to false

```
$client->addUser('john', 'password', true);
```

```json
OK
{"john"}
```

#### removeUser | Removes a user from the extension

```php
$client->removeUser('user', 'password');
```

```json
OK
```

## Events & Logging

### Event Listeners

You can add listeners to hook into the built-in event system, for all list of all available events, look inside the
`NeoEvents.php` file.

A listener can be a \Closure instance, an object implementing an __invoke method,
a string representing a function, or an array representing an object method or a class method.

Event listeners are currently not configurable with the yaml file, it will come soon...

```php
$client = ClientBuilder::create()
    ->addDefaultLocalConnection()
    ->addEventListener('foo.action', function (Event $event))
    ->build();
```

### Logging

You can add your logging system or ask the library to use the default built-in logging mechanism (currently only stream and ChromePHP
are supported).

If you integrate your own logging, he must be compatible with the PSR-3 standard.

```php
// Adding your own logging
$client = ClientBuilder::create()
    ->addDefaultLocalConnection()
    ->setLogger('app', MyLogger) // My Logger must implement Psr\Log\LoggerInterface
    ->build();
```

The library is shipped with two default Monolog handlers that you can use : Stream and ChromePHP. Registering them is straightforward :

```php

$client = ClientBuilder::create()
    ->addDefaultLocalConnection()
    ->createDefaultStreamLogger('name', '/path/to/your/log/file.log', 'debug')
    ->createDefaultChromePHPLogger('app', 'debug');
    ->build();
```

## Extending NeoClient


### Creating your own commands

You can extend the library by creating your own commands.

Create your `Command` class, this class must extend `Neoxygen\NeoClient\Command\AbstractCommand` and must implement
the `execute` method.

By extending the AbstractCommand class, you have access to the http client, and also the connection alias that is used
when invoking the command.

The best way to execute a command is by calling the `send` request of the HttpClient and passing the `method`, `path`,
`body` and `connectionAlias` arguments :

```php
<?php

namespace Acme;

use Neoxygen\NeoClient\Command\AbstractCommand;

/**
* Class that is used to get the extensions listed in the API
*/
class MyCommand extends AbstractCommand
{
    public function execute()
    {
        $method = 'GET';
        $path = '/db/data/extensions';

        // The arguments for the send method of the http client are
        // $method, $path, $body = null, $connectionAlias = null

        return $this->httpClient->send($method, $path, null, $this->connection);
    }
}
```

Then you have to register your command when building the client by passing an alias for your command and the class FQDN :

```php
$client = ClientBuilder::create()
    ->addDefaultLocalConnection()
    ->registerCommand('my_super_command', 'My\Command\Class\Namespace')
    ->build();
```

Then to use your command, just use the invoke method of the client :

```php
$command = $client->invoke('custom_get_extensions');
$extensions = $command->execute();
print_r($extensions);
```

### Creating an Extension

When you have a lot of commands, it may be good to create a command extension. Creating a command extension is quite simple :

You need to create a class that extends the `Neoxygen\NeoClient\Extension\AbsractExtension`, and you have to
implement the `getAvailableCommands` method that return an array of command aliases bounded to command classes :

```php

use Neoxygen\NeoClient\Extension\NeoClientExtensionInterface;

class MyExtension implements NeoClientExtensionInterface
{
    public static function getAvailableCommands()
    {
        return array(
            'custom_get_extensions' => 'My\Command\Class',
            'custom_other_exec' => 'My\Other\Class'
            );
    }
}
```

And then register your extension when building the client by giving an alias and the class FQDN of your extension :

```php
$client = ClientBuilder::create()
    ->addDefaultLocalConnection()
    ->registerExtension('my_extension_alias', 'My\Extension\Class\Namespace')
    ->build();
```

## Production settings

The library uses a Dependency Injenction Container and service files definitions, while this provide full flexibility and
robust code, this comes at a price.

By providing a cache path where the container and all the configuration can be dumped, you'll have the best of both worlds.

```yaml
connections:
  default:
    scheme: http
    host: localhost
    port: 7474
  testdb:
    scheme: http
    host: testserver.dev
    port: 7475

cache:
  enable: true
  cache_path: /dev/project/cache/
```

Don't forget to add full permissions to the cache path : `chmod -R 777 your/cache/path` and also to empty the cache dir when
you do changes to your configuration.

### Configuration Reference

### YAML

```yaml
connections:
  default:
    scheme: http
    host: localhost
    port: 7474
  testdb:
    scheme: http
    host: testserver.dev
    port: 7475
    auth: true
    user: user
    password: password

ha_mode:
    enabled: true
    type: community|enterprise
    master: default
    slaves:
        - testdb

auto_format_response: true

cache:
  enabled: true
  cache_path: /dev/project/cache

custom_commands:
  my_command:
    class: My\Command\Class

extensions:
  my_extension:
    class: My\Extension\Class
```

### License

The library is released under the MIT License, refer to the LICENSE file.

### Tests

To run the test suite, you need to copy the `tests/database_settings.yml.dist` to `tests/database_settings.yml`, as it will
create nodes to a real database.

Run `vendor/bin/phpunit`




