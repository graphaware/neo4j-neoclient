<?php

/*
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\HttpDriver\Result;

use GraphAware\Common\Cypher\StatementInterface;
use GraphAware\Common\Result\ResultSummaryInterface;

class ResultSummary implements ResultSummaryInterface
{
    /**
     * @var StatementInterface
     */
    protected $statement;

    /**
     * @var StatementStatistics
     */
    protected $updateStatistics;

    protected $notifications;

    protected $type;

    /**
     * {@inheritdoc}
     */
    public function __construct(StatementInterface $statement)
    {
        $this->statement = $statement;
    }

    /**
     * {@inheritdoc}
     */
    public function statement()
    {
        return $this->statement;
    }

    /**
     * {@inheritdoc}
     */
    public function updateStatistics()
    {
        return $this->updateStatistics;
    }

    /**
     * {@inheritdoc}
     */
    public function notifications()
    {
        return $this->notifications;
    }

    /**
     * {@inheritdoc}
     */
    public function statementType()
    {
        return $this->type;
    }

    /**
     * @param StatementStatistics $statistics
     */
    public function setStatistics(StatementStatistics $statistics)
    {
        $this->updateStatistics = $statistics;
    }
}
