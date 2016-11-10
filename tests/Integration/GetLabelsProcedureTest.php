<?php

/*
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\Tests;

use GraphAware\Neo4j\Client\Schema\Label;
use GraphAware\Neo4j\Client\Tests\Integration\IntegrationTestCase;

/**
 * Class GetLabelsProcedureTest.
 *
 * @group procedure
 */
class GetLabelsProcedureTest extends IntegrationTestCase
{
    public function testCanGetLabels()
    {
        $this->emptyDb();
        $this->client->run('CREATE (:Label1), (:Label2), (:Label3)');
        $result = $this->client->getLabels();
        $this->assertCount(3, $result);
        foreach ($result as $label) {
            $this->assertInstanceOf(Label::class, $label);
        }
    }
}
