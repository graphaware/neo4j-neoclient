<?php

/*
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client;

use GraphAware\Common\Result\AbstractRecordCursor;
use GraphAware\Neo4j\Client\Connection\ConnectionManager;
use GraphAware\Neo4j\Client\Exception\Neo4jException;
use GraphAware\Neo4j\Client\Result\ResultCollection;
use GraphAware\Neo4j\Client\Schema\Label;
use GraphAware\Neo4j\Client\Transaction\Transaction;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Interface ClientInterface.
 */
interface ClientInterface
{
    /**
     * Run a Cypher statement against the default database or the database specified.
     *
     * @param $query
     * @param null|array  $parameters
     * @param null|string $tag
     * @param null|string $connectionAlias
     *
     * @throws \GraphAware\Neo4j\Client\Exception\Neo4jExceptionInterface
     *
     * @return \GraphAware\Common\Result\Result
     */
    public function run($query, $parameters = null, $tag = null, $connectionAlias = null);

    /**
     * @param string      $query
     * @param null|array  $parameters
     * @param null|string $tag
     *
     * @throws Neo4jException
     *
     * @return AbstractRecordCursor
     */
    public function runWrite($query, $parameters = null, $tag = null);

    /**
     * @deprecated since 4.0 - will be removed in 5.0 - use <code>$client->runWrite()</code> instead
     *
     * @param string      $query
     * @param null|array  $parameters
     * @param null|string $tag
     *
     * @throws Neo4jException
     *
     * @return AbstractRecordCursor
     */
    public function sendWriteQuery($query, $parameters = null, $tag = null);

    /**
     * @param string|null $tag
     * @param string|null $connectionAlias
     *
     * @return Stack
     */
    public function stack($tag = null, $connectionAlias = null);

    /**
     * @param StackInterface $stack
     *
     * @throws Neo4jException
     *
     * @return ResultCollection|null
     */
    public function runStack(StackInterface $stack);

    /**
     * @param null|string $connectionAlias
     *
     * @return Transaction
     */
    public function transaction($connectionAlias = null);

    /**
     * @param string|null $conn
     *
     * @return Label[]
     */
    public function getLabels($conn = null);

    /**
     * @deprecated since 4.0 - will be removed in 5.0 - use <code>$client->run()</code> instead
     *
     * @param string      $query
     * @param null|array  $parameters
     * @param null|string $tag
     * @param null|string $connectionAlias
     *
     * @return AbstractRecordCursor
     */
    public function sendCypherQuery($query, $parameters = null, $tag = null, $connectionAlias = null);

    /**
     * @return ConnectionManager
     */
    public function getConnectionManager();

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher();
}
