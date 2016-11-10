<?php

/*
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\tests\Unit\Connection;

use GraphAware\Neo4j\Client\Connection\Connection;
use GraphAware\Neo4j\Client\HttpDriver\Driver as HttpDriver;

/**
 * @group unit
 * @group connection
 */
class ConnectionUnitTest extends \PHPUnit_Framework_TestCase
{
    public function testConnectionInstantiation()
    {
        $connection = new Connection('default', 'http://localhost:7474', null, 5);
        $this->assertInstanceOf(HttpDriver::class, $connection->getDriver());
    }
}
