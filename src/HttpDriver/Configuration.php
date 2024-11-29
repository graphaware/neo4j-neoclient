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
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\RequestFactory;
use GraphAware\Common\Connection\BaseConfiguration;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Configuration extends BaseConfiguration implements ConfigInterface
{
    /**
     * @var int
     * @deprecated Will be removed in 5.0
     */
    protected $timeout;

    /**
     * @var string
     * @deprecated Will be removed in 5.0
     */
    protected $curlInterface;

    /**
     * @param HttpClient|null $httpClient
     * @param RequestFactory|null $requestFactory
     * @return Configuration
     */
    public static function create(HttpClient $httpClient = null, RequestFactory $requestFactory = null)
    {
        return new self([
            'http_client' => $httpClient ?: HttpClientDiscovery::find(),
            'request_factory' => $requestFactory ?: MessageFactoryDiscovery::find(),
        ]);
    }

    /**
     * @param HttpClient $httpClient
     *
     * @return Configuration
     */
    public function setHttpClient(HttpClient $httpClient)
    {
        return $this->setValue('http_client', $httpClient);
    }

    /**
     * @param RequestFactory $requestFactory
     *
     * @return Configuration
     */
    public function setRequestFactory(RequestFactory $requestFactory)
    {
        return $this->setValue('request_factory', $requestFactory);
    }

    /**
     * @param int $timeout
     *
     * @return Configuration
     * @deprecated Will be removed in 5.0. The Timeout option will disappear.
     */
    public function withTimeout($timeout)
    {
        return $this->setValue('timeout', $timeout);
    }

    /**
     * @param string $interface
     *
     * @return $this
     * @deprecated Will be removed in 5.0. The CurlInterface option will disappear.
     */
    public function withCurlInterface($interface)
    {
        return $this->setValue('curl_interface', $interface);
    }

    /**
     * @return int
     * @deprecated Will be removed in 5.0
     */
    public function getTimeout()
    {
        return $this->getValue('timeout');
    }

    /**
     * @return string
     * @deprecated Will be removed in 5.0.
     */
    public function getCurlInterface()
    {
        return $this->getValue('curl_interface');
    }
}
