<?php

/*
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\HttpDriver;

use GraphAware\Common\Cypher\Statement;
use GraphAware\Common\Driver\PipelineInterface;

class Pipeline implements PipelineInterface
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Statement[]
     */
    protected $statements = [];

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function push($query, array $parameters = [], $tag = null)
    {
        $this->statements[] = Statement::create($query, $parameters, $tag);
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        return $this->session->flush($this);
    }

    /**
     * @return Statement[]
     */
    public function statements()
    {
        return $this->statements;
    }

    /**
     * @return int
     */
    public function size()
    {
        return count($this->statements);
    }
}
