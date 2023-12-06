<?php

namespace GraphAware\Neo4j\Client\Tests\Issues;

use GraphAware\Neo4j\Client\Tests\Integration\IntegrationTestCase;

/**
 * Class Issue99Test
 * @package GraphAware\Neo4j\Client\Tests\Issues
 *
 * @group issue-99
 */
class Issue99Test extends IntegrationTestCase
{
    public function testEmptyArrayCanBePassedAsParameter()
    {
        $q = 'CREATE (n:Person) SET n += {props} RETURN id(n)';
        $params = ['props' => ['id' => 123, 'some' => []]];

        $result = $this->client->run($q, $params);

        $this->assertEquals(1, $result->size());
    }
}