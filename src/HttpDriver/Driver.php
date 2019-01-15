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
use GraphAware\Common\Driver\DriverInterface;
use Http\Adapter\Guzzle6\Client;

class Driver implements DriverInterface
{
    const DEFAULT_HTTP_PORT = 7474;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @param string            $uri
     * @param BaseConfiguration $config
     */
    public function __construct($uri, ConfigInterface $config = null)
    {
        $this->uri = $uri;
        $this->config = null !== $config ? $config : Configuration::create();
    }

    /**
     * @return Session
     */
    public function session()
    {
        return new Session($this->uri, $this->getHttpClient(), $this->config);
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     *
     * @return \Http\Client\HttpClient
     */
    private function getHttpClient()
    {
        $options = [];
        if ($this->config->hasValue('timeout')) {
            $options['timeout'] = $this->config->getValue('timeout');
        }

        if ($this->config->hasValue('curl_interface')) {
            $options['curl'][10062] = $this->config->getValue('curl_interface');
        }

        if (empty($options)) {
            return $this->config->getValue('http_client');
        }

        // This is to keep BC. Will be removed in 5.0

        $options['curl'][74] = true;
        $options['curl'][75] = true;

        return Client::createWithConfig($options);
    }
}
