<?php

/*
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\tests\Issues;

use GraphAware\Neo4j\Client\Exception\Neo4jException;
use GraphAware\Neo4j\Client\Tests\Integration\IntegrationTestCase;
use Symfony\Component\Yaml\Exception\RuntimeException;

class ReportedIssuesTest extends IntegrationTestCase
{
    /**
     * @group issue-so-2
     */
    public function testTryingToDeleteNodeWithRelsInTransactionShouldFail()
    {
        $this->emptyDb();
        $this->createNodeWithRels();
        $tx = $this->client->transaction();
        $tx->push('MATCH (n:Node) DELETE n');
        $this->setExpectedException(Neo4jException::class);
        $tx->commit();
    }

    /**
     * @group issue-so-3
     */
    public function testTryingToDeleteNodeWithRelsInTransactionShouldFailAndTxBeRolledBack()
    {
        $this->emptyDb();
        $this->createNodeWithRels();
        $tx = $this->client->transaction();
        $tx->push('MATCH (n:Node) DELETE n');
        try {
            $tx->commit();
            // it should fail
            throw new RuntimeException();
        } catch (Neo4jException $e) {
            $this->assertTrue($tx->isRolledBack());
        }
    }

    private function createNodeWithRels()
    {
        $this->client->run('CREATE (n:Node)-[:REL]->(:OtherNode)');
    }
}
