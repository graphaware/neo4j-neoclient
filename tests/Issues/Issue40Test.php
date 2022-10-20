<?php

/*
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\Tests\Issues;

use GraphAware\Neo4j\Client\Tests\Integration\IntegrationTestCase;

/**
 * Class Issue40Test.
 *
 * @group issues
 */
class Issue40Test extends IntegrationTestCase
{
    public function testIssue()
    {
        $this->emptyDb();
        $this->client->run('CREATE (:BRIEF {id: 123})');

        $query = 'MATCH (s:BRIEF {id: $brief_id})
    CREATE (n:BRIEFNOTECARD)
    SET n += $data
    CREATE (n)-[:CARD_OF {order:0}]->(s)
    RETURN n';

        $parameters = [
            'brief_id' => 123,
            'data' => [
                'key_1' => 'test',
                'key_2' => 'other',
            ],
        ];

        $this->client->run($query, $parameters);
        $this->assertGraphExist('(n:BRIEF {id: 123})<-[:CARD_OF {order:0}]-(:BRIEFNOTECARD)');
    }

    private function assertGraphExist($pattern)
    {
        $q = sprintf('MATCH %s RETURN *', $pattern);
        $result = $this->client->run($q);

        $this->assertTrue(0 !== $result->size());
    }
}
