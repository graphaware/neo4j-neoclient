<?php

/*
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\tests\Integration;

/**
 * Class StatementParametersTest.
 *
 * @group params
 */
class StatementParametersTest extends IntegrationTestCase
{
    public function testEmptyArraysCanBeUsedAsNestedParameters()
    {
        $query = 'CREATE (a), (b)
        MERGE (a)-[r:RELATES]->(b)
        SET r += {fields} RETURN id(r) as id';

        $params = ['a' => 30, 'b' => 31, 'fields' => []];
        $result = $this->client->run($query, $params, null, 'http');
        $this->assertTrue(is_numeric($result->firstRecord()->get('id')));
    }

    public function testEmptyArraysInTransaction()
    {
        $query = 'CREATE (a), (b)
        MERGE (a)-[r:RELATES]->(b)
        SET r += {fields} RETURN id(r) as id';

        $params = ['a' => 30, 'b' => 31, 'fields' => []];
        $tx = $this->client->transaction('http');
        $tx->push($query, $params);
        $results = $tx->commit();

        $this->assertTrue(is_numeric($results->results()[0]->firstRecord()->get('id')));
    }
}
