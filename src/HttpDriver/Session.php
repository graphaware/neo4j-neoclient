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

use GraphAware\Common\Driver\ConfigInterface;
use GraphAware\Common\Driver\SessionInterface;
use GraphAware\Common\Transaction\TransactionInterface;
use GraphAware\Neo4j\Client\Exception\Neo4jException;
use GraphAware\Neo4j\Client\Formatter\ResponseFormatter;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

class Session implements SessionInterface
{
    /**
     * @var string
     */
    protected $uri;

    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * @var ResponseFormatter
     */
    protected $responseFormatter;

    /**
     * @var TransactionInterface|null
     */
    public $transaction;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @param string          $uri
     * @param Client          $httpClient
     * @param ConfigInterface $config
     */
    public function __construct($uri, Client $httpClient, ConfigInterface $config)
    {
        $this->uri = $uri;
        $this->httpClient = $httpClient;
        $this->responseFormatter = new ResponseFormatter();
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function run($statement, array $parameters = [], $tag = null)
    {
        $parameters = is_array($parameters) ? $parameters : [];
        $pipeline = $this->createPipeline($statement, $parameters, $tag);
        $response = $pipeline->run();

        return $response->results()[0];
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
    }

    /**
     * @return Transaction
     */
    public function transaction()
    {
        if ($this->transaction instanceof Transaction) {
            throw new \RuntimeException('A transaction is already bound to this session');
        }

        return new Transaction($this);
    }

    /**
     * @param string|null $query
     * @param array       $parameters
     * @param string|null $tag
     *
     * @return Pipeline
     */
    public function createPipeline($query = null, array $parameters = [], $tag = null)
    {
        $pipeline = new Pipeline($this);

        if (null !== $query) {
            $pipeline->push($query, $parameters, $tag);
        }

        return $pipeline;
    }

    /**
     * @param Pipeline $pipeline
     *
     * @throws \GraphAware\Neo4j\Client\Exception\Neo4jException
     *
     * @return \GraphAware\Common\Result\ResultCollection
     */
    public function flush(Pipeline $pipeline)
    {
        $request = $this->prepareRequest($pipeline);
        try {
            $response = $this->httpClient->send($request);
            $data = json_decode((string) $response->getBody(), true);
            if (!empty($data['errors'])) {
                $msg = sprintf('Neo4j Exception with code "%s" and message "%s"', $data['errors'][0]['code'], $data['errors'][0]['message']);
                $exception = new Neo4jException($msg);
                $exception->setNeo4jStatusCode($data['errors'][0]['code']);

                throw $exception;
            }
            $results = $this->responseFormatter->format(json_decode($response->getBody(), true), $pipeline->statements());

            return $results;
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $body = json_decode($e->getResponse()->getBody(), true);
                if (!isset($body['code'])) {
                    throw $e;
                }
                $msg = sprintf('Neo4j Exception with code "%s" and message "%s"', $body['errors'][0]['code'], $body['errors'][0]['message']);
                $exception = new Neo4jException($msg);
                $exception->setNeo4jStatusCode($body['errors'][0]['code']);

                throw $exception;
            }

            throw $e;
        }
    }

    /**
     * @param Pipeline $pipeline
     *
     * @return Request
     */
    public function prepareRequest(Pipeline $pipeline)
    {
        $statements = [];
        foreach ($pipeline->statements() as $statement) {
            $st = [
                'statement' => $statement->text(),
                'resultDataContents' => ['REST', 'GRAPH'],
                'includeStats' => true,
            ];
            if (!empty($statement->parameters())) {
                $st['parameters'] = $this->formatParams($statement->parameters());
            }
            $statements[] = $st;
        }

        $body = json_encode([
            'statements' => $statements,
        ]);
        $headers = [
            [
                'X-Stream' => true,
                'Content-Type' => 'application/json',
            ],
        ];

        return new Request('POST', sprintf('%s/db/data/transaction/commit', $this->uri), $headers, $body);
    }

    private function formatParams(array $params)
    {
        foreach ($params as $key => $v) {
            if (is_array($v)) {
                if (empty($v)) {
                    $params[$key] = new \stdClass();
                } else {
                    $params[$key] = $this->formatParams($params[$key]);
                }
            }
        }

        return $params;
    }

    /**
     * @throws Neo4jException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function begin()
    {
        $request = new Request('POST', sprintf('%s/db/data/transaction', $this->uri));

        try {
            return $this->httpClient->send($request);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $body = json_decode($e->getResponse()->getBody(), true);
                if (!isset($body['code'])) {
                    throw $e;
                }
                $msg = sprintf('Neo4j Exception with code "%s" and message "%s"', $body['errors'][0]['code'], $body['errors'][0]['message']);
                $exception = new Neo4jException($msg);
                $exception->setNeo4jStatusCode($body['errors'][0]['code']);

                throw $exception;
            }

            throw $e;
        }
    }

    /**
     * @param int   $transactionId
     * @param array $statementsStack
     *
     * @throws Neo4jException
     *
     * @return \GraphAware\Common\Result\ResultCollection
     */
    public function pushToTransaction($transactionId, array $statementsStack)
    {
        $statements = [];
        foreach ($statementsStack as $statement) {
            $st = [
                'statement' => $statement->text(),
                'resultDataContents' => ['REST', 'GRAPH'],
                'includeStats' => true,
            ];
            if (!empty($statement->parameters())) {
                $st['parameters'] = $this->formatParams($statement->parameters());
            }
            $statements[] = $st;
        }

        $headers = [
            [
                'X-Stream' => true,
                'Content-Type' => 'application/json',
            ],
        ];

        $body = json_encode([
            'statements' => $statements,
        ]);

        $request = new Request('POST', sprintf('%s/db/data/transaction/%d', $this->uri, $transactionId), $headers, $body);

        try {
            $response = $this->httpClient->send($request);
            $data = json_decode((string) $response->getBody(), true);
            if (!empty($data['errors'])) {
                $msg = sprintf('Neo4j Exception with code "%s" and message "%s"', $data['errors'][0]['code'], $data['errors'][0]['message']);
                $exception = new Neo4jException($msg);
                $exception->setNeo4jStatusCode($data['errors'][0]['code']);

                throw $exception;
            }

            return $this->responseFormatter->format(json_decode($response->getBody(), true), $statementsStack);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $body = json_decode($e->getResponse()->getBody(), true);
                if (!isset($body['code'])) {
                    throw $e;
                }
                $msg = sprintf('Neo4j Exception with code "%s" and message "%s"', $body['errors'][0]['code'], $body['errors'][0]['message']);
                $exception = new Neo4jException($msg);
                $exception->setNeo4jStatusCode($body['errors'][0]['code']);

                throw $exception;
            }

            throw $e;
        }
    }

    /**
     * @param int $transactionId
     *
     * @throws Neo4jException
     */
    public function commitTransaction($transactionId)
    {
        $request = new Request('POST', sprintf('%s/db/data/transaction/%d/commit', $this->uri, $transactionId));
        try {
            $response = $this->httpClient->send($request);
            $data = json_decode((string) $response->getBody(), true);
            if (!empty($data['errors'])) {
                $msg = sprintf('Neo4j Exception with code "%s" and message "%s"', $data['errors'][0]['code'], $data['errors'][0]['message']);
                $exception = new Neo4jException($msg);
                $exception->setNeo4jStatusCode($data['errors'][0]['code']);
                throw $exception;
            }
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $body = json_decode($e->getResponse()->getBody(), true);
                if (!isset($body['code'])) {
                    throw $e;
                }
                $msg = sprintf('Neo4j Exception with code "%s" and message "%s"', $body['errors'][0]['code'], $body['errors'][0]['message']);
                $exception = new Neo4jException($msg);
                $exception->setNeo4jStatusCode($body['errors'][0]['code']);

                throw $exception;
            }

            throw $e;
        }
    }

    /**
     * @param int $transactionId
     *
     * @throws Neo4jException
     */
    public function rollbackTransaction($transactionId)
    {
        $request = new Request('DELETE', sprintf('%s/db/data/transaction/%d', $this->uri, $transactionId));

        try {
            $this->httpClient->send($request);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $body = json_decode($e->getResponse()->getBody(), true);
                if (!isset($body['code'])) {
                    throw $e;
                }
                $msg = sprintf('Neo4j Exception with code "%s" and message "%s"', $body['errors'][0]['code'], $body['errors'][0]['message']);
                $exception = new Neo4jException($msg);
                $exception->setNeo4jStatusCode($body['errors'][0]['code']);

                throw $exception;
            }

            throw $e;
        }
    }
}
