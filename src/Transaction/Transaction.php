<?php

/*
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\Transaction;

use GraphAware\Common\Cypher\Statement;
use GraphAware\Common\Transaction\TransactionInterface;
use GraphAware\Neo4j\Client\StackInterface;

class Transaction
{
    /**
     * @var TransactionInterface
     */
    private $driverTransaction;

    /**
     * @var Statement[]
     */
    protected $queue = [];

    /**
     * @param TransactionInterface $driverTransaction
     */
    public function __construct(TransactionInterface $driverTransaction)
    {
        $this->driverTransaction = $driverTransaction;
    }

    /**
     * Push a statement to the queue, without actually sending it.
     *
     * @param string      $statement
     * @param array       $parameters
     * @param string|null $tag
     */
    public function push($statement, array $parameters = [], $tag = null)
    {
        $this->queue[] = Statement::create($statement, $parameters, $tag);
    }

    /**
     * @param string      $statement
     * @param array       $parameters
     * @param null|string $tag
     *
     * @return \GraphAware\Common\Result\Result
     */
    public function run($statement, array $parameters = [], $tag = null)
    {
        if (!$this->driverTransaction->isOpen() && !in_array($this->driverTransaction->status(), ['COMMITED', 'ROLLED_BACK'], true)) {
            $this->driverTransaction->begin();
        }

        return $this->driverTransaction->run(Statement::create($statement, $parameters, $tag));
    }

    /**
     * Push a statements Stack to the queue, without actually sending it.
     *
     * @param \GraphAware\Neo4j\Client\StackInterface $stack
     */
    public function pushStack(StackInterface $stack)
    {
        $this->queue[] = $stack;
    }

    /**
     * @param StackInterface $stack
     *
     * @return mixed
     */
    public function runStack(StackInterface $stack)
    {
        if (!$this->driverTransaction->isOpen() && !in_array($this->driverTransaction->status(), ['COMMITED', 'ROLLED_BACK'], true)) {
            $this->driverTransaction->begin();
        }

        $sts = [];

        foreach ($stack->statements() as $statement) {
            $sts[] = $statement;
        }

        return $this->driverTransaction->runMultiple($sts);
    }

    public function begin()
    {
        $this->driverTransaction->begin();
    }

    /**
     * @return bool
     */
    public function isOpen()
    {
        return $this->driverTransaction->isOpen();
    }

    /**
     * @return bool
     */
    public function isCommited()
    {
        return $this->driverTransaction->isCommited();
    }

    /**
     * @return bool
     */
    public function isRolledBack()
    {
        return $this->driverTransaction->isRolledBack();
    }

    /**
     * @return string
     */
    public function status()
    {
        return $this->driverTransaction->status();
    }

    /**
     * @return mixed
     */
    public function commit()
    {
        if (!$this->driverTransaction->isOpen() && !in_array($this->driverTransaction->status(), ['COMMITED', 'ROLLED_BACK'], true)) {
            $this->driverTransaction->begin();
        }
        if (!empty($this->queue)) {
            $stack = [];
            foreach ($this->queue as $element) {
                if ($element instanceof StackInterface) {
                    foreach ($element->statements() as $statement) {
                        $stack[] = $statement;
                    }
                } else {
                    $stack[] = $element;
                }
            }

            $result = $this->driverTransaction->runMultiple($stack);
            $this->driverTransaction->commit();
            $this->queue = [];

            return $result;
        }

        return $this->driverTransaction->commit();
    }

    public function rollback()
    {
        return $this->driverTransaction->rollback();
    }
}
