<?php

/*
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\Connection;

use GraphAware\Neo4j\Client\HttpDriver\Configuration;

class ConnectionManager
{
    /**
     * @var array Array of all registered connections
     */
    private $connections = [];

    /**
     * @var Connection|null
     */
    private $master;

    /**
     * @param string             $alias
     * @param string             $uri
     * @param null|Configuration $config
     */
    public function registerConnection($alias, $uri, $config = null)
    {
        $this->registerExistingConnection($alias, new Connection($alias, $uri, $config));
    }

    /**
     * @param string     $alias
     * @param Connection $connection
     */
    public function registerExistingConnection($alias, Connection $connection)
    {
        $this->connections[$alias] = $connection;
    }

    /**
     * @param null $alias
     *
     * @return \GraphAware\Neo4j\Client\Connection\Connection
     */
    public function getConnection($alias = null)
    {
        if (null === $alias) {
            list($a) = array_keys($this->connections);

            return $this->connections[$a];
        }

        if (!array_key_exists($alias, $this->connections)) {
            throw new \InvalidArgumentException(sprintf('The connection "%s" is not registered', $alias));
        }

        return $this->connections[$alias];
    }

    /**
     * @param string $alias
     */
    public function setMaster($alias)
    {
        $this->master = $this->connections[$alias];
    }

    /**
     * @return Connection|null
     */
    public function getMasterConnection()
    {
        return $this->master;
    }
}
