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

use GraphAware\Common\Connection\BaseConfiguration;
use GraphAware\Common\Driver\ConfigInterface;
use GraphAware\Common\Driver\SessionInterface;
use GraphAware\Common\Transaction\TransactionInterface;
use GraphAware\Neo4j\Client\Exception\Neo4jException;
use GraphAware\Neo4j\Client\Formatter\ResponseFormatter;
use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle6\Client;
use Http\Client\Common\Plugin\ErrorPlugin;
use Http\Client\Common\PluginClient;
use Http\Client\Exception\HttpException;
use Http\Client\HttpClient;
use Http\Message\RequestFactory;
use Psr\Http\Message\RequestInterface;

class Session implements SessionInterface
{
    /**
     * @var string
     */
    protected $uri;

    /**
     * @var HttpClient
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
     * @var Configuration
     */
    protected $config;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @param string                  $uri
     * @param GuzzleClient|HttpClient $httpClient
     * @param ConfigInterface         $config
     */
    public function __construct($uri, $httpClient, ConfigInterface $config)
    {
        if ($httpClient instanceof GuzzleClient) {
            @trigger_error('Passing a Guzzle client to Session is deprecrated. Will be removed in 5.0. Use a HTTPlug client');
            $httpClient = new Client($httpClient);
        } elseif (!$httpClient instanceof HttpClient) {
            throw new \RuntimeException('Second argument to Session::__construct must be an instance of Http\Client\HttpClient.');
        }

        if (null !== $config && !$config instanceof BaseConfiguration) {
            throw new \RuntimeException(sprintf('Third argument to "%s" must be null or "%s"', __CLASS__, BaseConfiguration::class));
        }

        $this->uri = $uri;
        $this->httpClient = new PluginClient($httpClient, [new ErrorPlugin()]);
        $this->responseFormatter = new ResponseFormatter();
        $this->config = $config;
        $this->requestFactory = $config->getValue('request_factory');
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
            $response = $this->httpClient->sendRequest($request);
            $data = json_decode((string) $response->getBody(), true);
            if (!empty($data['errors'])) {
                $msg = sprintf('Neo4j Exception with code "%s" and message "%s"', $data['errors'][0]['code'], $data['errors'][0]['message']);
                $exception = new Neo4jException($msg);
                $exception->setNeo4jStatusCode($data['errors'][0]['code']);

                throw $exception;
            }

            return $this->responseFormatter->format($data, $pipeline->statements());
        } catch (HttpException $e) {
            $body = json_decode($e->getResponse()->getBody(), true);
            if (!isset($body['code'])) {
                throw $e;
            }
            $msg = sprintf('Neo4j Exception with code "%s" and message "%s"', $body['errors'][0]['code'], $body['errors'][0]['message']);
            $exception = new Neo4jException($msg, 0, $e);
            $exception->setNeo4jStatusCode($body['errors'][0]['code']);

            throw $exception;
        }
    }

    /**
     * @param Pipeline $pipeline
     *
     * @return RequestInterface
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
            'X-Stream' => true,
            'Content-Type' => 'application/json',
        ];

        return $this->requestFactory->createRequest('POST', sprintf('%s/db/data/transaction/commit', $this->uri), $headers, $body);
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
        $request = $this->requestFactory->createRequest('POST', sprintf('%s/db/data/transaction', $this->uri));

        try {
            return $this->httpClient->sendRequest($request);
        } catch (HttpException $e) {
            $body = json_decode($e->getResponse()->getBody(), true);
            if (!isset($body['code'])) {
                throw $e;
            }
            $msg = sprintf('Neo4j Exception with code "%s" and message "%s"', $body['errors'][0]['code'], $body['errors'][0]['message']);
            $exception = new Neo4jException($msg, 0, $e);
            $exception->setNeo4jStatusCode($body['errors'][0]['code']);

            throw $exception;
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
            'X-Stream' => true,
            'Content-Type' => 'application/json',
        ];

        $body = json_encode([
            'statements' => $statements,
        ]);

        $request = $this->requestFactory->createRequest('POST', sprintf('%s/db/data/transaction/%d', $this->uri, $transactionId), $headers, $body);

        try {
            $response = $this->httpClient->sendRequest($request);
            $data = json_decode((string) $response->getBody(), true);
            if (!empty($data['errors'])) {
                $msg = sprintf('Neo4j Exception with code "%s" and message "%s"', $data['errors'][0]['code'], $data['errors'][0]['message']);
                $exception = new Neo4jException($msg);
                $exception->setNeo4jStatusCode($data['errors'][0]['code']);

                throw $exception;
            }

            return $this->responseFormatter->format($data, $statementsStack);
        } catch (HttpException $e) {
            $body = json_decode($e->getResponse()->getBody(), true);
            if (!isset($body['code'])) {
                throw $e;
            }
            $msg = sprintf('Neo4j Exception with code "%s" and message "%s"', $body['errors'][0]['code'], $body['errors'][0]['message']);
            $exception = new Neo4jException($msg, 0, $e);
            $exception->setNeo4jStatusCode($body['errors'][0]['code']);

            throw $exception;
        }
    }

    /**
     * @param int $transactionId
     *
     * @throws Neo4jException
     */
    public function commitTransaction($transactionId)
    {
        $request = $this->requestFactory->createRequest('POST', sprintf('%s/db/data/transaction/%d/commit', $this->uri, $transactionId));
        try {
            $response = $this->httpClient->sendRequest($request);
            $data = json_decode((string) $response->getBody(), true);
            if (!empty($data['errors'])) {
                $msg = sprintf('Neo4j Exception with code "%s" and message "%s"', $data['errors'][0]['code'], $data['errors'][0]['message']);
                $exception = new Neo4jException($msg);
                $exception->setNeo4jStatusCode($data['errors'][0]['code']);
                throw $exception;
            }
        } catch (HttpException $e) {
            $body = json_decode($e->getResponse()->getBody(), true);
            if (!isset($body['code'])) {
                throw $e;
            }
            $msg = sprintf('Neo4j Exception with code "%s" and message "%s"', $body['errors'][0]['code'], $body['errors'][0]['message']);
            $exception = new Neo4jException($msg, 0, $e);
            $exception->setNeo4jStatusCode($body['errors'][0]['code']);

            throw $exception;
        }
    }

    /**
     * @param int $transactionId
     *
     * @throws Neo4jException
     */
    public function rollbackTransaction($transactionId)
    {
        $request = $this->requestFactory->createRequest('DELETE', sprintf('%s/db/data/transaction/%d', $this->uri, $transactionId));

        try {
            $this->httpClient->sendRequest($request);
        } catch (HttpException $e) {
            $body = json_decode($e->getResponse()->getBody(), true);
            if (!isset($body['code'])) {
                throw $e;
            }
            $msg = sprintf('Neo4j Exception with code "%s" and message "%s"', $body['errors'][0]['code'], $body['errors'][0]['message']);
            $exception = new Neo4jException($msg, 0, $e);
            $exception->setNeo4jStatusCode($body['errors'][0]['code']);

            throw $exception;
        }
    }
}
