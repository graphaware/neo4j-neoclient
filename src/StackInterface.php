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

use GraphAware\Common\Cypher\Statement;

/**
 * Interface StackInterface.
 */
interface StackInterface
{
    /**
     * @param null|string $tag
     * @param null|string $connectionAlias
     *
     * @return Stack
     */
    public static function create($tag = null, $connectionAlias = null);

    /**
     * @param string     $query
     * @param null|array $parameters
     * @param null|array $tag
     */
    public function push($query, $parameters = null, $tag = null);

    /**
     * @param string     $query
     * @param null|array $parameters
     * @param null|array $tag
     */
    public function pushWrite($query, $parameters = null, $tag = null);

    /**
     * @param string     $query
     * @param array|null $parameters
     * @param array|null $tag
     */
    public function addPreflight($query, $parameters = null, $tag = null);

    /**
     * @return bool
     */
    public function hasPreflights();

    /**
     * @return \GraphAware\Common\Cypher\Statement[]
     */
    public function getPreflights();

    /**
     * @return int
     */
    public function size();

    /**
     * @return Statement[]
     */
    public function statements();

    /**
     * @return null|string
     */
    public function getTag();

    /**
     * @return null|string
     */
    public function getConnectionAlias();

    /**
     * @return bool
     */
    public function hasWrites();
}
