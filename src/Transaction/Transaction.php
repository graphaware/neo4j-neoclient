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

use GraphAware\Bolt\Exception\MessageFailureException;
use GraphAware\Common\Cypher\Statement;
use GraphAware\Common\Result\Result;
use GraphAware\Common\Transaction\TransactionInterface;
use GraphAware\Neo4j\Client\Event\FailureEvent;
use GraphAware\Neo4j\Client\Event\PostRunEvent;
use GraphAware\Neo4j\Client\Event\PreRunEvent;
use GraphAware\Neo4j\Client\Exception\Neo4jException;
use GraphAware\Neo4j\Client\Neo4jClientEvents;
use GraphAware\Neo4j\Client\Result\ResultCollection;
use GraphAware\Neo4j\Client\StackInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param TransactionInterface $driverTransaction
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(TransactionInterface $driverTransaction, EventDispatcherInterface $eventDispatcher)
    {
        $this->driverTransaction = $driverTransaction;
        $this->eventDispatcher = $eventDispatcher;
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
     * @throws Neo4jException
     *
     * @return \GraphAware\Common\Result\Result|null
     */
    public function run($statement, array $parameters = [], $tag = null)
    {
        if (!$this->driverTransaction->isOpen() && !in_array($this->driverTransaction->status(), ['COMMITED', 'ROLLED_BACK'], true)) {
            $this->driverTransaction->begin();
        }
        $stmt = Statement::create($statement, $parameters, $tag);
        $this->eventDispatcher->dispatch(new PreRunEvent([$stmt]), Neo4jClientEvents::NEO4J_PRE_RUN);
        try {
            $result = $this->driverTransaction->run(Statement::create($statement, $parameters, $tag));
            $this->eventDispatcher->dispatch(new PostRunEvent(ResultCollection::withResult($result)), Neo4jClientEvents::NEO4J_POST_RUN);
        } catch (MessageFailureException $e) {
            $exception = new Neo4jException($e->getMessage());
            $exception->setNeo4jStatusCode($e->getStatusCode());

            $event = new FailureEvent($exception);
            $this->eventDispatcher->dispatch($event, Neo4jClientEvents::NEO4J_ON_FAILURE);
            if ($event->shouldThrowException()) {
                throw $exception;
            }
            return null;
        }

        return $result;
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
     * @throws Neo4jException
     *
     * @return ResultCollection|Result[]|null
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

        $this->eventDispatcher->dispatch(Neo4jClientEvents::NEO4J_PRE_RUN, new PreRunEvent($stack->statements()));
        try {
            $results = $this->driverTransaction->runMultiple($sts);
            $this->eventDispatcher->dispatch(Neo4jClientEvents::NEO4J_POST_RUN, new PostRunEvent($results));
        } catch (MessageFailureException $e) {
            $exception = new Neo4jException($e->getMessage());
            $exception->setNeo4jStatusCode($e->getStatusCode());

            $event = new FailureEvent($exception);
            $this->eventDispatcher->dispatch(Neo4jClientEvents::NEO4J_ON_FAILURE, $event);
            if ($event->shouldThrowException()) {
                throw $exception;
            }
            return null;
        }

        return $results;
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
