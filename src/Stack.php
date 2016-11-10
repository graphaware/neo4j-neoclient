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

class Stack implements StackInterface
{
    /**
     * @var null|string
     */
    protected $tag;

    /**
     * @var string
     */
    protected $connectionAlias;

    /**
     * @var Statement[]
     */
    protected $statements = [];

    /**
     * @var Statement[]
     */
    protected $preflights = [];

    /**
     * @param null        $tag
     * @param null|string $connectionAlias
     */
    public function __construct($tag = null, $connectionAlias = null)
    {
        $this->tag = null !== $tag ? (string) $tag : null;
        $this->connectionAlias = $connectionAlias;
    }

    /**
     * @param null|string $tag
     * @param null|string $connectionAlias
     *
     * @return Stack
     */
    public static function create($tag = null, $connectionAlias = null)
    {
        return new static($tag, $connectionAlias);
    }

    /**
     * @param string     $query
     * @param null|array $parameters
     * @param null|array $tag
     */
    public function push($query, $parameters = null, $tag = null)
    {
        $params = null !== $parameters ? $parameters : [];
        $this->statements[] = Statement::create($query, $params, $tag);
    }

    /**
     * @param $query
     * @param array|null $parameters
     * @param array|null $tag
     */
    public function addPreflight($query, $parameters = null, $tag = null)
    {
        $params = null !== $parameters ? $parameters : [];
        $this->preflights[] = Statement::create($query, $params, $tag);
    }

    /**
     * @return bool
     */
    public function hasPreflights()
    {
        return !empty($this->preflights);
    }

    /**
     * @return \GraphAware\Common\Cypher\Statement[]
     */
    public function getPreflights()
    {
        return $this->preflights;
    }

    /**
     * @return int
     */
    public function size()
    {
        return count($this->statements);
    }

    /**
     * @return Statement[]
     */
    public function statements()
    {
        return $this->statements;
    }

    /**
     * @return null|string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @return null|string
     */
    public function getConnectionAlias()
    {
        return $this->connectionAlias;
    }
}
