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
use GraphAware\Common\Driver\DriverInterface;
use GuzzleHttp\Client;

class Driver implements DriverInterface
{
    /**
     * @var string
     */
    protected $uri;

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @param string        $uri
     * @param Configuration $config
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
        $options = [];
        if (null !== $this->config->getTimeout()) {
            $options['timeout'] = $this->config->getTimeout();
        }

        if (null !== $this->config->getCurlInterface()) {
            $options['curl'][10062] = $this->config->getCurlInterface();
        }

        $options['curl'][74] = true;
        $options['curl'][75] = true;

        return new Session(
            $this->uri, new Client($options), $this->config);
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }
}
